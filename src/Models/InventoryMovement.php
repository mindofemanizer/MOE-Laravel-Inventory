<?php

declare(strict_types=1);

namespace Moe\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryMovement extends Model
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'inventory_id',
        'type',
        'quantity',
        'reason',
        'reference_type',
        'reference_id',
        'balance_before',
        'balance_after',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('inventory.tables.movements', 'inventory_movements');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isInbound(): bool
    {
        return $this->quantity > 0;
    }

    public function isOutbound(): bool
    {
        return $this->quantity < 0;
    }
}
