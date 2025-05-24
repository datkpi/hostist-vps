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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique(); // Liên kết với bảng users
            $table->string('company_name')->nullable();
            $table->string('tax_code')->nullable();
            $table->string('business_type')->nullable();
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->string('source')->nullable();

            // Fields cho quản lý số dư
            $table->decimal('balance', 15, 2)->default(0.00); // Số dư tài khoản
            $table->string('wallet_id')->nullable(); // ID ví điện tử nếu cần

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
