<?php

declare(strict_types=1);

namespace Moe\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Moe\Inventory\Contracts\RestockableInterface;
use Moe\Inventory\Contracts\StockableInterface;
use Moe\Inventory\Exceptions\StockNotAvailable;

class Inventory extends Model implements StockableInterface, RestockableInterface
{
    use SoftDeletes;

    protected $table;

    protected $fillable = [
        'product_id',
        'quantity',
        'minimum_stock',
        'maximum_stock',
        'last_restock_at',
        'last_sold_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_stock' => 'integer',
        'maximum_stock' => 'integer',
        'last_restock_at' => 'datetime',
        'last_sold_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('inventory.tables.inventories', 'inventories');
    }

    public function inventory(): static
    {
        return $this;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(config('inventory.models.product', 'App\\Models\\Product'));
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_id');
    }

    public function getStock(): int
    {
        return $this->quantity;
    }

    public function isStockAvailable(int $quantity): bool
    {
        return $this->quantity >= $quantity;
    }

    public function incrementStock(int $quantity, ?string $reason = null): void
    {
        DB::transaction(function () use ($quantity, $reason) {
            $this->increment('quantity', $quantity);
            $this->update(['last_restock_at' => now()]);
            $after = $this->fresh()->quantity;

            $this->movements()->create([
                'type' => 'in',
                'quantity' => $quantity,
                'reason' => $reason,
                'balance_before' => $after - $quantity,
                'balance_after' => $after,
            ]);
        });
    }

    public function decrementStock(int $quantity, ?string $reason = null): void
    {
        DB::transaction(function () use ($quantity, $reason) {
            $fresh = $this->fresh();

            if (! $fresh || (int) $fresh->quantity < $quantity) {
                throw new StockNotAvailable(
                    "Stok tidak mencukupi. Dibutuhkan: {$quantity}, Tersedia: " . ($fresh?->quantity ?? 0)
                );
            }

            $fresh->decrement('quantity', $quantity);
            $fresh->update(['last_sold_at' => now()]);
            $after = $fresh->fresh()?->quantity ?? 0;

            $fresh->movements()->create([
                'type' => 'out',
                'quantity' => -$quantity,
                'reason' => $reason,
                'balance_before' => $after + $quantity,
                'balance_after' => $after,
            ]);
        });
    }

    public function restock(int $quantity, ?string $reference = null): void
    {
        $this->incrementStock($quantity, $reference ?? 'restock');
    }

    public function getMinimumStock(): int
    {
        return $this->minimum_stock;
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }
}
