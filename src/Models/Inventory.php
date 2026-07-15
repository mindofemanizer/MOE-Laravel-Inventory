<?php

namespace Moe\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Moe\Inventory\Contracts\StockableInterface;
use Moe\Inventory\Contracts\RestockableInterface;

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

    public function product()
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
        $this->increment('quantity', $quantity);
        $this->update(['last_restock_at' => now()]);

        $this->movements()->create([
            'type' => 'in',
            'quantity' => $quantity,
            'reason' => $reason,
            'balance_before' => $this->fresh()->quantity - $quantity,
            'balance_after' => $this->fresh()->quantity,
        ]);
    }

    public function decrementStock(int $quantity, ?string $reason = null): void
    {
        if (! $this->isStockAvailable($quantity)) {
            throw new \Moe\Core\Exceptions\StockNotAvailable(
                "Stok tidak mencukupi. Dibutuhkan: {$quantity}, Tersedia: {$this->quantity}"
            );
        }

        $this->decrement('quantity', $quantity);
        $this->update(['last_sold_at' => now()]);

        $this->movements()->create([
            'type' => 'out',
            'quantity' => -$quantity,
            'reason' => $reason,
            'balance_before' => $this->fresh()->quantity + $quantity,
            'balance_after' => $this->fresh()->quantity,
        ]);
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
