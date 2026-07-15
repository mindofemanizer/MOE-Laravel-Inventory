<?php

namespace Moe\Inventory\Contracts;

interface RestockableInterface
{
    public function restock(int $quantity, ?string $reference = null): void;
    public function getMinimumStock(): int;
    public function isLowStock(): bool;
    public function isOutOfStock(): bool;
}
