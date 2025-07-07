<?php

use App\Models\category;
use App\Models\product;
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
        Schema::create('category_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(product::class)->constrained()->cascadeOnDelete();
            // $table->unsignedBigInteger('category_id');
            // $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreignIdFor(category::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_products');
    }
};
