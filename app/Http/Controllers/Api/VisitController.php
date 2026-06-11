<?php

namespace App\Http\Controllers\Api;

use App\Actions\RecordVisit;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecordVisitRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Throwable;

class VisitController extends Controller
{
    /**
     * Record a visit
     *
     * Called by the physical device each time a customer is detected entering the shop.
     * Creates the customer on first encounter. Increments `trees_planted` every
     * `VISITS_PER_TREE` visits for that customer.
     *
     * @throws Throwable
     *
     * @response 201 {
     *   "visit_id": 1,
     *   "customer_id": 1,
     *   "visited_at": "2026-06-11T14:30:00+00:00"
     * }
     */
    public function store(RecordVisitRequest $request, RecordVisit $action): JsonResponse
    {
        $validated = $request->validated();

        $visitedAt = isset($validated['visited_at'])
            ? Carbon::parse($validated['visited_at'])
            : Carbon::now();

        $visit = $action->handle(
            externalId: $validated['external_id'],
            visitedAt: $visitedAt,
            name: $validated['name'] ?? null,
        );

        return response()->json([
            'visit_id' => $visit->id,
            'customer_id' => $visit->customer_id,
            'visited_at' => $visit->visited_at->toIso8601String(),
        ], 201);
    }
}
