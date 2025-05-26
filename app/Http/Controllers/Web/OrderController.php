<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Config;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function showOrder($id)
    {
        $order = Orders::with(['items.product', 'customer'])->findOrFail($id);

        // Kiểm tra quyền truy cập
        if (!Auth::user()->customer || Auth::user()->customer->id != $order->customer_id) {
            return redirect()->route('customer.orders')->with('error', 'Bạn không có quyền truy cập đơn hàng này');
        }

        // Lấy thông tin công ty
        $config = Config::current();

        // Bạn có thể sử dụng file view hiện có hoặc tạo file view mới
        return view('source.web.invoice.show_order', compact('order', 'config'));
    }
}
