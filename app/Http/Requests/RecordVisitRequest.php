<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            /**
             * Stable unique identifier for the customer (e.g. card number, UUID).
             *
             * @example card-0042
             */
            'external_id' => ['required', 'string', 'max:255'],

            /**
             * ISO 8601 timestamp of detection. Defaults to current server time if omitted.
             *
             * @example 2026-06-11T14:30:00Z
             */
            'visited_at' => ['sometimes', 'date'],

            /**
             * Display name. Stored only on first encounter; ignored on subsequent visits.
             *
             * @example Jane Doe
             */
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
