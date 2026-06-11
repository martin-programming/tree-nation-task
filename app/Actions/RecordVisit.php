<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecordVisit
{
    /**
     * @throws Throwable
     */
    public function handle(string $externalId, Carbon $visitedAt, ?string $name = null): Visit
    {
        return DB::transaction(function () use ($externalId, $visitedAt, $name): Visit {
            $customer = Customer::query()->firstOrCreate(
                ['external_id' => $externalId],
                ['name' => $name],
            );

            // Lock the row to prevent concurrent counter drift for the same customer.
            $customer = Customer::query()->where('id', $customer->id)->lockForUpdate()->first();

            $visit = $customer->visits()->create(['visited_at' => $visitedAt]);

            $customer->total_visits += 1;
            $customer->last_visited_at = $visitedAt;

            $visitsPerTree = (int) config('app.visits_per_tree', 10);

            if ($customer->total_visits % $visitsPerTree === 0) {
                $customer->trees_planted += 1;
            }

            $customer->save();

            return $visit;
        });
    }
}
