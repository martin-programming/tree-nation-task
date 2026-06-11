<?php

namespace App\Actions;

use App\Models\Customer;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetDashboardStats
{
    /**
     * @return array{
     *     visitsByHour: Collection<int, array{hour: string, count: int}>,
     *     totalVisitsToday: int,
     *     totalTreesPlanted: int,
     *     totalCustomers: int,
     * }
     */
    public function handle(): array
    {
        return [
            'visitsByHour' => $this->visitsByHour(),
            'totalVisitsToday' => $this->totalVisitsToday(),
            'totalTreesPlanted' => $this->totalTreesPlanted(),
            'totalCustomers' => $this->totalCustomers(),
        ];
    }

    /**
     * @return Collection<int, array{hour: string, count: int}>
     */
    private function visitsByHour(): Collection
    {
        $since = Carbon::now()->subHours(24)->startOfHour();

        $truncExpr = DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%dT%H:00:00', visited_at) AS hour"
            : "DATE_TRUNC('hour', visited_at) AS hour";

        return Visit::query()
            ->selectRaw("$truncExpr, COUNT(*) AS count")
            ->where('visited_at', '>=', $since)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn ($row) => [
                'hour' => Carbon::parse($row->hour)->toIso8601String(),
                'count' => (int) $row->count,
            ])
            ->values();
    }

    private function totalVisitsToday(): int
    {
        return Visit::query()
            ->where('visited_at', '>=', Carbon::now()->startOfDay())
            ->count();
    }

    private function totalTreesPlanted(): int
    {
        return (int) Customer::query()->sum('trees_planted');
    }

    private function totalCustomers(): int
    {
        return Customer::query()->count();
    }
}
