<?php

namespace App\Http\Controllers\auth;

use App\Repositories\AuthRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function showLoginForm()
    {
        // Nếu đã đăng nhập, điều hướng dựa vào vai trò
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        return view('source.admin.auth.login');
    }

    public function showRegisterForm()
    {
        // Nếu đã đăng nhập, điều hướng dựa vào vai trò
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        return view('source.admin.auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $result = $this->authRepository->login($credentials);

        if ($result['success']) {
            // Kiểm tra có URL intended không
            if (session()->has('url.intended')) {
                $intended = session('url.intended');
                session()->forget('url.intended');
                return redirect($intended);
            }

            // Nếu không có, điều hướng dựa vào vai trò
            return $this->redirectBasedOnRole();
        }

        return back()->withErrors([
            'email' => $result['message'],
        ]);
    }

    public function logout()
    {
        $this->authRepository->logout();
        return redirect('/login');
    }

    /**
     * Điều hướng người dùng dựa vào vai trò
     */
    protected function redirectBasedOnRole()
    {
        // Lấy user hiện tại
        $user = Auth::user();

        // Kiểm tra role
        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        // Người dùng thường
        return redirect()->route('homepage');
    }
    /**
     * Xử lý đăng ký người dùng
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'tax_code' => 'nullable|string|max:50',
        ]);

        // Sử dụng AuthRepository để đăng ký
        $result = $this->authRepository->register([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'tax_code' => $validated['tax_code'] ?? null,
        ]);

        if ($result['success']) {
            // Đăng nhập user
            Auth::login($result['user']);

            // Điều hướng về trang chủ
            return redirect()->route('homepage')->with('success', 'Đăng ký thành công!');
        }

        return back()->withErrors([
            'email' => $result['message'],
        ])->withInput($request->except('password', 'password_confirmation'));
    }
}
