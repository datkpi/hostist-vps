<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Categories;

class PricingController extends Controller
{
    public function index() 
    {
        // Lấy tất cả danh mục có sản phẩm
        $categories = Categories::with(['products' => function($query) {
            $query->forSale() // Chỉ lấy sản phẩm để bán
                  ->orderBy('sort_order')
                  ->orderBy('name');
        }])
        ->where('status', 'active')
        ->whereHas('products', function($query) {
            $query->forSale();
        })
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        // Hoặc lấy sản phẩm nhóm theo danh mục
        $productsByCategory = Products::forSale()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category.name');

        // Lấy sản phẩm nổi bật
        $featuredProducts = Products::forSale()
            ->where('is_featured', true)
            ->with('category')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $compacts = [
            'categories' => $categories,
            'productsByCategory' => $productsByCategory,
            'featuredProducts' => $featuredProducts
        ];
        
        return view('source.web.pricing.index', $compacts);
    }
}