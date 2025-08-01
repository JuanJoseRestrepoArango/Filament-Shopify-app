<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('variantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('productos_id')->constrained('productos')->cascadeOnDelete();
            $table->string('nombre')->nullable();
            $table->decimal('precio',10,2)->nullable();
            $table->string('shopify_variante_id')->index()->nullable();
            $table->string('inventario_item_id')->index()->nullable();
            $table->integer('stock')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variantes');
    }
};
