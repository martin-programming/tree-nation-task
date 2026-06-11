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
     * @throws Throwable
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
