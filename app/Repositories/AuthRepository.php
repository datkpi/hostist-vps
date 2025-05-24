<?php

namespace App\Repositories;

use App\Models\Customers;
use App\Repositories\Support\AbstractRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthRepository extends AbstractRepository
{
    public function model()
    {
        return User::class;
    }

    public function login(array $credentials)
    {
        try {
            if (!Auth::attempt($credentials)) {
                return [
                    'success' => false,
                    'message' => 'Email hoặc mật khẩu không chính xác'
                ];
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'success' => true,
                'user' => $user,
                'token' => $token
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Đăng nhập thất bại: ' . $e->getMessage()
            ];
        }
    }

    // App\Repositories\AuthRepository.php
    public function register(array $data)
    {
        try {
            // Tạo user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'role' => 'user', // Mặc định là user thường
                'is_active' => true,
            ]);

            // Tạo customer cho user
            $customer = Customers::create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'] ?? $data['name'],
                'tax_code' => $data['tax_code'] ?? null,
                'business_type' => $data['business_type'] ?? 'individual',
                'industry' => $data['industry'] ?? null,
                'website' => $data['website'] ?? null,
                'status' => 'active',
                'source' => 'website',
            ]);

            return [
                'success' => true,
                'user' => $user,
                'customer' => $customer
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Đăng ký thất bại: ' . $e->getMessage()
            ];
        }
    }

    public function logout()
    {
        try {
            Auth::user()->tokens()->delete();
            Auth::logout();

            return [
                'success' => true,
                'message' => 'Đăng xuất thành công'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Đăng xuất thất bại: ' . $e->getMessage()
            ];
        }
    }
}
