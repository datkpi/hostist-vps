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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Thông tin cá nhân bổ sung
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->text('address')->nullable();

            // Trường cho phân quyền
            $table->enum('role', ['user', 'admin', 'super_admin'])->default('user');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();

            // Token và timestamps
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Cho phép xóa mềm
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
