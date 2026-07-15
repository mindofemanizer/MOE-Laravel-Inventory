<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model Bindings
    |--------------------------------------------------------------------------
    */
    'models' => [

        'product' => App\Models\Product::class,

        'inventory' => Moe\Inventory\Models\Inventory::class,

        'movement' => Moe\Inventory\Models\InventoryMovement::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */
    'tables' => [

        'inventories' => 'inventories',

        'movements' => 'inventory_movements',

    ],

    /*
    |--------------------------------------------------------------------------
    | Movement Types
    |--------------------------------------------------------------------------
    */
    'movement_types' => [

        'in' => [
            'restock',
            'return',
            'adjustment',
            'initial',
        ],

        'out' => [
            'sale',
            'return',
            'adjustment',
            'damage',
        ],

    ],

];
