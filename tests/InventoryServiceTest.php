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

    public function test_can_create_inventory()
    {
        $inventory = $this->service->createInventory(1, 100);

        $this->assertInstanceOf(Inventory::class, $inventory);
        $this->assertEquals(1, $inventory->product_id);
        $this->assertEquals(100, $inventory->quantity);
    }

    public function test_can_add_stock()
    {
        $inventory = $this->service->createInventory(1, 100);
        $this->service->addStock($inventory, 50, 'restock', 'Restok barang');

        $this->assertEquals(150, $inventory->fresh()->quantity);
    }

    public function test_can_remove_stock()
    {
        $inventory = $this->service->createInventory(1, 100);
        $this->service->removeStock($inventory, 30, 'sale', 'Terjual');

        $this->assertEquals(70, $inventory->fresh()->quantity);
    }

    public function test_cannot_remove_stock_below_zero()
    {
        $this->expectException(\Exception::class);

        $inventory = $this->service->createInventory(1, 10);
        $this->service->removeStock($inventory, 20, 'sale', 'Terjual');
    }
}
