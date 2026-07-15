<?php

namespace Moe\Inventory\Tests;

use Moe\Inventory\Models\Inventory;
use Moe\Inventory\Services\InventoryService;

class InventoryServiceTest extends TestCase
{
    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventoryService();
    }

    public function test_can_get_or_create_inventory()
    {
        $inventory = $this->service->getInventory(1);

        $this->assertInstanceOf(Inventory::class, $inventory);
        $this->assertEquals(1, $inventory->product_id);
        $this->assertEquals(0, $inventory->quantity);
    }

    public function test_can_increment_stock()
    {
        $this->service->incrementStock(1, 50, 'Restok barang');

        $inventory = $this->service->getInventory(1);
        $this->assertEquals(50, $inventory->quantity);
    }

    public function test_can_decrement_stock()
    {
        $inventory = $this->service->getInventory(1);
        $inventory->update(['quantity' => 100]);

        $this->service->decrementStock(1, 30, 'Terjual');

        $this->assertEquals(70, $this->service->getInventory(1)->quantity);
    }

    public function test_is_stock_available()
    {
        $this->service->incrementStock(1, 10, 'Restok');

        $this->assertTrue($this->service->isStockAvailable(1, 5));
        $this->assertFalse($this->service->isStockAvailable(1, 15));
    }

    public function test_low_stock_detection()
    {
        $inventory = $this->service->getInventory(1);
        $inventory->update(['quantity' => 5, 'minimum_stock' => 10]);

        $lowStock = $this->service->getLowStockProducts();
        $this->assertCount(1, $lowStock);
    }
}
