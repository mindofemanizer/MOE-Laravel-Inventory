<?php

namespace Moe\Inventory\Contracts;

interface StockableInterface
{
    public function inventory();
    public function getStock(): int;
    public function isStockAvailable(int $quantity): bool;
    public function incrementStock(int $quantity, ?string $reason = null): void;
    public function decrementStock(int $quantity, ?string $reason = null): void;
}
