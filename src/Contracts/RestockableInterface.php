<?php

declare(strict_types=1);

namespace Moe\Inventory\Contracts;

interface RestockableInterface
{
    /**
     * Restock the inventory.
     *
     * @param int $quantity
     * @param string|null $reference
     * @return void
     */
    public function restock(int $quantity, ?string $reference = null): void;

    /**
     * Get the minimum stock level.
     *
     * @return int
     */
    public function getMinimumStock(): int;

    /**
     * Check if stock is low.
     *
     * @return bool
     */
    public function isLowStock(): bool;

    /**
     * Check if stock is out.
     *
     * @return bool
     */
    public function isOutOfStock(): bool;
}
