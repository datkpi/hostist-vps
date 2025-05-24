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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('parent_product_id')->nullable();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('sku')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('image')->nullable();
            $table->string('type')->default('product'); // product, service, ssl, domain, hosting
            $table->string('product_status')->default('active');
            $table->string('service_status')->nullable();
            $table->integer('stock')->default(-1); // -1 = unlimited
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->integer('recurring_period')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->text('meta_data')->nullable();
            $table->text('options')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')
                ->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')
                ->onDelete('cascade');
            $table->foreign('parent_product_id')->references('id')->on('products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
