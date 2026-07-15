<?php

namespace Moe\Inventory\Services;

use Illuminate\Support\Facades\DB;
use Moe\Core\Base\BaseService;
use Moe\Inventory\Models\Inventory;
use Moe\Inventory\Models\InventoryMovement;

class InventoryService extends BaseService
{
    /**
     * Get or create inventory for a product.
     */
    public function getInventory(int $productId): Inventory
    {
        return Inventory::firstOrCreate(
            ['product_id' => $productId],
            [
                'quantity' => 0,
                'minimum_stock' => 10,
                'last_restock_at' => now(),
            ]
        );
    }

    /**
     * Check if stock is available.
     */
    public function isStockAvailable(int $productId, int $quantity): bool
    {
        $inventory = $this->getInventory($productId);

        return $inventory->isStockAvailable($quantity);
    }

    /**
     * Get current stock.
     */
    public function getStock(int $productId): int
    {
        return $this->getInventory($productId)->getStock();
    }

    /**
     * Increment stock (restock).
     */
    public function incrementStock(int $productId, int $quantity, ?string $reason = null): void
    {
        $inventory = $this->getInventory($productId);
        $inventory->incrementStock($quantity, $reason);
    }

    /**
     * Decrement stock (sale).
     */
    public function decrementStock(int $productId, int $quantity, ?string $reason = null): void
    {
        $inventory = $this->getInventory($productId);
        $inventory->decrementStock($quantity, $reason);
    }

    /**
     * Get products with low stock.
     */
    public function getLowStockProducts(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Inventory::whereColumn('quantity', '<=', 'minimum_stock')
            ->with('product')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products out of stock.
     */
    public function getOutOfStockProducts(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Inventory::where('quantity', '<=', 0)
            ->with('product')
            ->limit($limit)
            ->get();
    }

    /**
     * Get stock movements.
     */
    public function getMovements(int $inventoryId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return InventoryMovement::where('inventory_id', $inventoryId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk update stock (for import).
     */
    public function bulkUpdate(array $updates): void
    {
        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                $inventory = $this->getInventory($update['product_id']);
                $inventory->update(['quantity' => $update['quantity']]);
            }
        });
    }
}
