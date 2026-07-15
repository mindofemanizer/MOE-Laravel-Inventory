<?php

declare(strict_types=1);

namespace Moe\Inventory\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Moe\Core\Base\BaseService;
use Moe\Inventory\Models\Inventory;
use Moe\Inventory\Models\InventoryMovement;

class InventoryService extends BaseService
{
    /**
     * Get or create inventory for a product.
     *
     * @param int $productId
     * @return \Moe\Inventory\Models\Inventory
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
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function isStockAvailable(int $productId, int $quantity): bool
    {
        $inventory = $this->getInventory($productId);

        return $inventory->isStockAvailable($quantity);
    }

    /**
     * Get current stock.
     *
     * @param int $productId
     * @return int
     */
    public function getStock(int $productId): int
    {
        return $this->getInventory($productId)->getStock();
    }

    /**
     * Increment stock (restock).
     *
     * @param int $productId
     * @param int $quantity
     * @param string|null $reason
     * @return void
     */
    public function incrementStock(int $productId, int $quantity, ?string $reason = null): void
    {
        $inventory = $this->getInventory($productId);
        $inventory->incrementStock($quantity, $reason);
    }

    /**
     * Decrement stock (sale).
     *
     * @param int $productId
     * @param int $quantity
     * @param string|null $reason
     * @return void
     *
     * @throws \Moe\Inventory\Exceptions\StockNotAvailable
     */
    public function decrementStock(int $productId, int $quantity, ?string $reason = null): void
    {
        $inventory = $this->getInventory($productId);
        $inventory->decrementStock($quantity, $reason);
    }

    /**
     * Get products with low stock.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(int $limit = 50): Collection
    {
        return Inventory::whereColumn('quantity', '<=', 'minimum_stock')
            ->with('product')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products out of stock.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOutOfStockProducts(int $limit = 50): Collection
    {
        return Inventory::where('quantity', '<=', 0)
            ->with('product')
            ->limit($limit)
            ->get();
    }

    /**
     * Get stock movements.
     *
     * @param int $inventoryId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMovements(int $inventoryId, int $limit = 50): Collection
    {
        return InventoryMovement::where('inventory_id', $inventoryId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk update stock (for import).
     *
     * @param array $updates
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function bulkUpdate(array $updates): void
    {
        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                if (! isset($update['product_id'], $update['quantity'])) {
                    throw new \InvalidArgumentException('Each update must contain product_id and quantity');
                }

                $inventory = $this->getInventory((int) $update['product_id']);
                $inventory->update(['quantity' => (int) $update['quantity']]);
            }
        });
    }
}
