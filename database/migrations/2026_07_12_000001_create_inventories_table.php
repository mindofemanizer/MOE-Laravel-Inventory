<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique();
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(10);
            $table->integer('maximum_stock')->nullable();
            $table->timestamp('last_restock_at')->nullable();
            $table->timestamp('last_sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
