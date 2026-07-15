<?php

namespace Moe\Inventory\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \Moe\Inventory\InventoryServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('inventory.models.product', \Moe\Inventory\Tests\Stubs\Product::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
