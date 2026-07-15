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

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('inventory.tables.inventories', 'inventories');
    }

    /**
     * Get the inventory instance (self).
     *
     * @return static
     */
    public function inventory(): static
    {
        return $this;
    }

    /**
     * Get the product relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(config('inventory.models.product', 'App\\Models\\Product'));
    }

    /**
     * Get the movements relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_id');
    }

    /**
     * Get the current stock quantity.
     *
     * @return int
     */
    public function getStock(): int
    {
        return $this->quantity;
    }

    /**
     * Check if stock is available for the given quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function isStockAvailable(int $quantity): bool
    {
        return $this->quantity >= $quantity;
    }

    /**
     * Increment stock by the given quantity.
     *
     * @param int $quantity
     * @param string|null $reason
     * @return void
     */
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

    /**
     * Decrement stock by the given quantity.
     *
     * @param int $quantity
     * @param string|null $reason
     * @return void
     *
     * @throws \Moe\Inventory\Exceptions\StockNotAvailable
     */
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

    /**
     * Restock the inventory.
     *
     * @param int $quantity
     * @param string|null $reference
     * @return void
     */
    public function restock(int $quantity, ?string $reference = null): void
    {
        $this->incrementStock($quantity, $reference ?? 'restock');
    }

    /**
     * Get the minimum stock level.
     *
     * @return int
     */
    public function getMinimumStock(): int
    {
        return $this->minimum_stock;
    }

    /**
     * Check if stock is low.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_stock;
    }

    /**
     * Check if stock is out.
     *
     * @return bool
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }
}
