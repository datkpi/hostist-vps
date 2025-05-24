<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Helpers\PricingHelper;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng
     */
    public function index()
    {
        $cart = $this->getCart();

        return view('source.web.cart.index', compact('cart'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addToCart(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'options' => 'nullable|array',
                'custom_price' => 'nullable|numeric'
            ]);

            // Lấy thông tin sản phẩm
            $product = Products::findOrFail($request->product_id);
            $rules = [
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'options.period' => 'required|integer|in:1,2,3,5',
            ];

            // Thêm validation cho domain dựa vào loại sản phẩm
            if ($product->type == 'ssl' || $product->type == 'domain') {
                $rules['options.domain'] = 'required|string|max:255|regex:/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9](\.[a-zA-Z]{2,})+$/';
            }

            $validated = $request->validate($rules, [
                'options.domain.required' => 'Vui lòng nhập tên miền cho dịch vụ này',
                'options.domain.regex' => 'Tên miền không hợp lệ'
            ]);

            // Kiểm tra sản phẩm có hợp lệ không
            if ($product->customer_id || $product->product_status !== 'active') {
                return back()->with('error', 'Sản phẩm không có sẵn để mua');
            }

            // Lấy hoặc tạo giỏ hàng
            $cart = $this->getCart();

            // Lấy thông tin thời hạn
            $options = $request->options ?? [];
            $period = $options['period'] ?? 1;

            // Xác định giá theo thời hạn
            if ($request->has('custom_price')) {
                // Sử dụng giá từ form nếu có
                $price = $request->custom_price;
            } else {
                // Tính giá dựa theo thời hạn nếu không có giá tùy chỉnh
                $basePrice = $product->sale_price ?? $product->price;
                $price = $basePrice * $period;
            }

            // Tạo tên sản phẩm với thông tin thời hạn
            $productName = $product->name . " (" . $period . " năm)";

            // Kiểm tra xem có mục nào trong giỏ hàng với cùng product_id không
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                // Kiểm tra xem gói thời hạn đã được thêm vào giỏ hàng chưa
                $existingOptions = json_decode($existingItem->options, true) ?: [];
                $existingPeriod = $existingOptions['period'] ?? null;

                if ($existingPeriod == $period) {
                    // Nếu đã có cùng thời hạn, cập nhật số lượng
                    $existingItem->quantity += $request->quantity;
                    $existingItem->subtotal = $price * $existingItem->quantity;
                    $existingItem->total = $existingItem->subtotal;
                    $existingItem->save();

                    $message = 'Đã cập nhật số lượng sản phẩm trong giỏ hàng';
                } else {
                    // Nếu thời hạn khác, cập nhật thay thế thông tin cũ
                    $existingItem->name = $productName;
                    $existingItem->unit_price = $price;
                    $existingItem->options = json_encode($options);
                    $existingItem->quantity = $request->quantity;
                    $existingItem->subtotal = $price * $existingItem->quantity;
                    $existingItem->total = $existingItem->subtotal;
                    $existingItem->save();

                    $message = 'Đã cập nhật sản phẩm với thời hạn mới';
                }
            } else {
                // Tạo mới nếu chưa có sản phẩm này trong giỏ hàng
                $cartItem = new CartItem([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'name' => $productName,
                    'quantity' => $request->quantity,
                    'unit_price' => $price,
                    'subtotal' => $price * $request->quantity,
                    'total' => $price * $request->quantity,
                    'options' => json_encode($options)
                ]);
                $cartItem->save();

                $message = 'Đã thêm sản phẩm vào giỏ hàng';
            }

            // Cập nhật tổng giỏ hàng
            $this->updateCartTotals($cart);

            // Redirect với thông báo thành công
            return redirect()->route('cart.index')->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Xử lý lỗi validation
            return back()->withErrors($e->validator)->withInput()->with('error', 'Vui lòng kiểm tra lại thông tin nhập vào');
        } catch (\Illuminate\Database\QueryException $e) {
            // Xử lý lỗi database
            Log::error('Database error when adding to cart: ' . $e->getMessage());

            // Kiểm tra nếu là lỗi ràng buộc duy nhất
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // Xử lý trường hợp trùng lặp
                return $this->handleDuplicateCartItem($request, $product);
            }

            return back()->with('error', 'Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại sau.');
        } catch (\Exception $e) {
            // Xử lý các lỗi khác
            Log::error('Error adding to cart: ' . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra. Vui lòng thử lại sau.');
        }
    }

    /**
     * Xử lý trường hợp trùng lặp mục trong giỏ hàng
     */
    private function handleDuplicateCartItem(Request $request, $product)
    {
        try {
            $cart = $this->getCart();
            $options = $request->options ?? [];
            $period = $options['period'] ?? 1;

            // Tìm mục hiện có
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                // Xóa mục hiện có
                $existingItem->delete();

                // Thêm lại với thông tin mới
                return $this->addToCart($request);
            }

            return back()->with('error', 'Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.');
        } catch (\Exception $e) {
            Log::error('Error handling duplicate cart item: ' . $e->getMessage());
            return back()->with('error', 'Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại sau.');
        }
    }

    /**
     * Lấy giỏ hàng hiện tại hoặc tạo mới
     */
    private function getCart()
    {
        if (Auth::check()) {
            // Nếu đã đăng nhập, lấy giỏ hàng theo user_id
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                [
                    'expires_at' => now()->addDays(7),
                    'subtotal' => 0,
                    'total_amount' => 0
                ]
            );
        } else {
            // Nếu chưa đăng nhập, lấy giỏ hàng theo session_id
            $sessionId = session()->getId();
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId],
                [
                    'expires_at' => now()->addDays(7),
                    'subtotal' => 0,
                    'total_amount' => 0
                ]
            );
        }

        return $cart;
    }

    /**
     * Cập nhật tổng giỏ hàng
     */
    private function updateCartTotals(Cart $cart)
    {
        $items = CartItem::where('cart_id', $cart->id)->get();

        $subtotal = $items->sum('subtotal');
        $taxAmount = $items->sum('tax_amount');
        $discountAmount = $items->sum('discount_amount');
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $cart->subtotal = $subtotal;
        $cart->tax_amount = $taxAmount ?? 0;
        $cart->discount_amount = $discountAmount ?? 0;
        $cart->total_amount = $totalAmount;
        $cart->save();

        // Cập nhật số lượng sản phẩm trong giỏ hàng vào session
        session(['cart_count' => $items->sum('quantity')]);

        return $cart;
    }
    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        $cartItem = CartItem::findOrFail($itemId);
        $cart = $cartItem->cart;

        // Kiểm tra quyền truy cập
        if (!$this->checkCartAccess($cart)) {
            return back()->with('error', 'Bạn không có quyền truy cập vào giỏ hàng này');
        }

        // Cập nhật số lượng
        $cartItem->quantity = $request->quantity;
        $cartItem->subtotal = $cartItem->unit_price * $cartItem->quantity;
        $cartItem->total = $cartItem->subtotal;
        $cartItem->save();

        // Cập nhật tổng giỏ hàng
        $this->updateCartTotals($cart);

        return back()->with('success', 'Đã cập nhật giỏ hàng');
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeItem(Request $request, $itemId)
    {
        $cartItem = CartItem::findOrFail($itemId);
        $cart = $cartItem->cart;

        // Kiểm tra quyền truy cập
        if (!$this->checkCartAccess($cart)) {
            return back()->with('error', 'Bạn không có quyền truy cập vào giỏ hàng này');
        }

        // Xóa sản phẩm
        $cartItem->delete();

        // Cập nhật tổng giỏ hàng
        $this->updateCartTotals($cart);

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng');
    }

    /**
     * Xóa tất cả sản phẩm trong giỏ hàng
     */
    public function clearCart(Request $request)
    {
        $cart = $this->getCart();

        // Kiểm tra quyền truy cập
        if (!$this->checkCartAccess($cart)) {
            return back()->with('error', 'Bạn không có quyền truy cập vào giỏ hàng này');
        }

        // Xóa tất cả sản phẩm
        CartItem::where('cart_id', $cart->id)->delete();

        // Cập nhật tổng giỏ hàng
        $cart->subtotal = 0;
        $cart->tax_amount = 0;
        $cart->discount_amount = 0;
        $cart->total_amount = 0;
        $cart->save();

        // Cập nhật session
        session(['cart_count' => 0]);

        return back()->with('success', 'Đã xóa tất cả sản phẩm trong giỏ hàng');
    }

    /**
     * Kiểm tra quyền truy cập vào giỏ hàng
     */
    private function checkCartAccess(Cart $cart)
    {
        if (Auth::check()) {
            // Nếu đã đăng nhập, kiểm tra user_id
            return $cart->user_id == Auth::id();
        } else {
            // Nếu chưa đăng nhập, kiểm tra session_id
            return $cart->session_id == session()->getId();
        }
    }
}
