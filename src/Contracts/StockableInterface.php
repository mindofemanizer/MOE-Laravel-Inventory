<?php

declare(strict_types=1);

namespace Moe\Inventory\Contracts;

interface StockableInterface
{
    /**
     * Get the inventory instance.
     *
     * @return static
     */
    public function inventory(): static;

    /**
     * Get the current stock quantity.
     *
     * @return int
     */
    public function getStock(): int;

    /**
     * Check if stock is available for the given quantity.
     *
     * @param int $quantity
     * @return bool
     */
    public function isStockAvailable(int $quantity): bool;

    /**
     * Increment stock by the given quantity.
     *
     * @param int $quantity
     * @param string|null $reason
     * @return void
     */
    public function incrementStock(int $quantity, ?string $reason = null): void;

    /**
     * Decrement stock by the given quantity.
     *
     * @param int $quantity
     * @param string|null $reason
     * @return void
     */
    public function decrementStock(int $quantity, ?string $reason = null): void;
}
