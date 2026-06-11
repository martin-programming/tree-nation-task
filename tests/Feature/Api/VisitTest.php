<?php

namespace Tests\Feature\Api;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    private string $apiKey = 'test-api-key';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.api_key' => $this->apiKey]);
    }

    public function test_missing_api_key_returns_401(): void
    {
        $response = $this->postJson('/api/visits', [
            'external_id' => 'card-001',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized.']);
    }

    public function test_invalid_api_key_returns_401(): void
    {
        $response = $this->postJson('/api/visits', [
            'external_id' => 'card-001',
        ], ['Authorization' => 'Bearer wrong-key']);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized.']);
    }

    public function test_valid_api_key_records_visit(): void
    {
        $response = $this->postJson('/api/visits', [
            'external_id' => 'card-001',
        ], ['Authorization' => "Bearer {$this->apiKey}"]);

        $response->assertStatus(201)
            ->assertJsonStructure(['visit_id', 'customer_id', 'visited_at']);
    }

    public function test_rate_limit_returns_429(): void
    {
        RateLimiter::for('visits', fn () => Limit::perMinute(1)->by($this->apiKey));

        $this->postJson('/api/visits', [
            'external_id' => 'card-001',
        ], ['Authorization' => "Bearer {$this->apiKey}"])->assertStatus(201);

        $this->postJson('/api/visits', [
            'external_id' => 'card-002',
        ], ['Authorization' => "Bearer {$this->apiKey}"])->assertStatus(429);
    }
}
