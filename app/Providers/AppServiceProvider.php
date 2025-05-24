<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
          // Chia sẻ số lượng sản phẩm trong giỏ hàng với tất cả view
         View::composer('layouts.app', function ($view) {
            $cartItems = session('cart', []);
            $view->with('cart_count', count($cartItems));
        });
    }
}
