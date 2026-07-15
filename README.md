# MOE-Laravel-Inventory

Inventory module for MOE ecosystem — Stock, Warehouse, Restock.

## Installation

```bash
composer require moe/laravel-inventory
php artisan vendor:publish --provider="Moe\Inventory\InventoryServiceProvider" --tag="inventory-config"
php artisan vendor:publish --provider="Moe\Inventory\InventoryServiceProvider" --tag="inventory-migrations"
php artisan migrate
```

## What's Included

### Models

| Model | Table | Description |
|-------|-------|-------------|
| `Inventory` | `inventories` | Product stock with minimum/maximum |
| `InventoryMovement` | `inventory_movements` | Stock movement history |

### Services

| Service | Description |
|---------|-------------|
| `InventoryService` | Stock check, increment, decrement, bulk update |

### Contracts

| Contract | Description |
|----------|-------------|
| `StockableInterface` | Interface for stockable models |
| `RestockableInterface` | Interface for restockable models |

## Usage

### Check Stock

```php
use Moe\Inventory\Services\InventoryService;

$inventoryService = app(InventoryService::class);
$stock = $inventoryService->getStock($productId);
$isAvailable = $inventoryService->isStockAvailable($productId, 5);
```

### Increment Stock

```php
$inventoryService->incrementStock($productId, 100, 'Restock from vendor');
```

### Decrement Stock

```php
$inventoryService->decrementStock($productId, 2, 'Order #123');
```

### Get Low Stock Products

```php
$lowStock = $inventoryService->getLowStockProducts();
```

### Bulk Update

```php
$inventoryService->bulkUpdate([
    ['product_id' => 1, 'quantity' => 100],
    ['product_id' => 2, 'quantity' => 50],
]);
```

## Config

```php
// config/inventory.php
return [
    'models' => [
        'product' => App\Models\Product::class,
    ],
    'tables' => [
        'inventories' => 'inventories',
        'movements' => 'inventory_movements',
    ],
];
```

## Requirements

- PHP ^8.2
- Laravel ^12.0|^13.0
- `moe/laravel-core`

## License

MIT
