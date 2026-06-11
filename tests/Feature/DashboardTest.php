<?php

namespace Tests\Feature;

use App\Actions\GetDashboardStats;
use App\Models\Customer;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_publicly_accessible(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_returns_visits_by_hour(): void
    {
        $customer = Customer::factory()->create();

        Visit::factory()->create([
            'customer_id' => $customer->id,
            'visited_at' => Carbon::now()->subHour(),
        ]);

        $stats = app(GetDashboardStats::class)->handle();

        $this->assertNotEmpty($stats['visitsByHour']);
        $this->assertArrayHasKey('hour', $stats['visitsByHour']->first());
        $this->assertArrayHasKey('count', $stats['visitsByHour']->first());
    }

    public function test_dashboard_returns_total_visits_today(): void
    {
        $customer = Customer::factory()->create();

        Visit::factory()->create([
            'customer_id' => $customer->id,
            'visited_at' => Carbon::now(),
        ]);

        Visit::factory()->create([
            'customer_id' => $customer->id,
            'visited_at' => Carbon::yesterday(),
        ]);

        $stats = app(GetDashboardStats::class)->handle();

        $this->assertSame(1, $stats['totalVisitsToday']);
    }

    public function test_dashboard_returns_total_trees_planted(): void
    {
        Customer::factory()->create(['trees_planted' => 3]);
        Customer::factory()->create(['trees_planted' => 2]);

        $stats = app(GetDashboardStats::class)->handle();

        $this->assertSame(5, $stats['totalTreesPlanted']);
    }

    public function test_dashboard_returns_total_customers(): void
    {
        Customer::factory()->count(4)->create();

        $stats = app(GetDashboardStats::class)->handle();

        $this->assertSame(4, $stats['totalCustomers']);
    }
}
