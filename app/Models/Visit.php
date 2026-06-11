<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $customer_id
 * @property Carbon $visited_at
 * @property Carbon $created_at
 * @property-read Customer $customer
 */
class Visit extends Model
{
    use HasFactory;

    public const string|null UPDATED_AT = null;

    /**
     * @var array<string>
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
