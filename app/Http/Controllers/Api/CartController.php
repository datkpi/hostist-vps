<?php

namespace App\Http\Controllers\api;


use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartController extends Controller
{
    // Lấy giỏ hàng hiện tại hoặc tạo mới
    private function getCart($request)
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id()],
                [
                    'expires_at' => now()->addDays(7),
                    'subtotal' => 0,
                    'total_amount' => 0
                ]
            );
        } else {
            $sessionId = $request->cookie('cart_session') ?? Str::uuid();
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

    // Lấy thông tin giỏ hàng
    public function getCart(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load('items.product');

        return response()->json([
            'status' => 'success',
            'data' => $cart
        ]);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array'
        ]);

        $cart = $this->getCart($request);
        $product = Products::findOrFail($request->product_id);

        // Kiểm tra sản phẩm có sẵn để bán không
        if ($product->customer_id || $product->product_status !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Sản phẩm không có sẵn để bán'
            ], 400);
        }

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Cập nhật số lượng nếu đã tồn tại
            $cartItem->quantity += $request->quantity;
            $cartItem->options = $request->options ?? $cartItem->options;
            $cartItem->subtotal = $product->price * $cartItem->quantity;
            $cartItem->total = $cartItem->subtotal;
            $cartItem->save();
        } else {
            // Tạo mới nếu chưa tồn tại
            $cartItem = new CartItem([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'unit_price' => $product->price,
                'subtotal' => $product->price * $request->quantity,
                'total' => $product->price * $request->quantity,
                'options' => $request->options
            ]);
            $cartItem->save();
        }

        // Cập nhật tổng giỏ hàng
        $this->updateCartTotals($cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã thêm sản phẩm vào giỏ hàng',
            'data' => $cart->load('items.product')
        ]);
    }

    // Cập nhật sản phẩm trong giỏ hàng
    public function updateItem(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'options' => 'nullable|array'
        ]);

        $cart = $this->getCart($request);
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $cartItem->quantity = $request->quantity;
        $cartItem->options = $request->options ?? $cartItem->options;
        $cartItem->subtotal = $cartItem->unit_price * $cartItem->quantity;
        $cartItem->total = $cartItem->subtotal;
        $cartItem->save();

        // Cập nhật tổng giỏ hàng
        $this->updateCartTotals($cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã cập nhật giỏ hàng',
            'data' => $cart->load('items.product')
        ]);
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeItem(Request $request, $itemId)
    {
        $cart = $this->getCart($request);
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->firstOrFail();

        $cartItem->delete();

        // Cập nhật tổng giỏ hàng
        $this->updateCartTotals($cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
            'data' => $cart->load('items.product')
        ]);
    }

    // Xóa tất cả sản phẩm trong giỏ hàng
    public function clearCart(Request $request)
    {
        $cart = $this->getCart($request);

        CartItem::where('cart_id', $cart->id)->delete();

        $cart->subtotal = 0;
        $cart->tax_amount = 0;
        $cart->discount_amount = 0;
        $cart->total_amount = 0;
        $cart->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa tất cả sản phẩm trong giỏ hàng',
            'data' => $cart
        ]);
    }

    // Cập nhật tổng giỏ hàng
    private function updateCartTotals(Cart $cart)
    {
        $items = CartItem::where('cart_id', $cart->id)->get();

        $subtotal = $items->sum('subtotal');
        $taxAmount = $items->sum('tax_amount');
        $discountAmount = $items->sum('discount_amount');
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $cart->subtotal = $subtotal;
        $cart->tax_amount = $taxAmount;
        $cart->discount_amount = $discountAmount;
        $cart->total_amount = $totalAmount;
        $cart->save();

        return $cart;
    }

    // Chuyển đổi giỏ hàng thành đơn hàng
    public function checkout(Request $request)
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập để tiếp tục thanh toán'
            ], 401);
        }

        $cart = $this->getCart($request);

        // Kiểm tra giỏ hàng có sản phẩm không
        if ($cart->items->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Giỏ hàng trống, vui lòng thêm sản phẩm trước khi thanh toán'
            ], 400);
        }

        $user = Auth::user();
        $customer = $user->customer;

        // Kiểm tra và tạo customer nếu chưa có
        if (!$customer) {
            $customer = new Customers();
            $customer->user_id = $user->id;
            $customer->company_name = $user->name;
            $customer->status = 'active';
            $customer->source = 'website';
            $customer->balance = 0;
            $customer->save();
        }

        try {
            // Bắt đầu transaction
            DB::beginTransaction();

            // Tạo đơn hàng mới
            $order = new Orders([
                'order_number' => 'ORD-' . time() . Str::random(5),
                'customer_id' => $customer->id,
                'status' => 'pending',
                'subtotal' => $cart->subtotal,
                'tax_amount' => $cart->tax_amount,
                'discount_amount' => $cart->discount_amount,
                'total_amount' => $cart->total_amount,
                'created_by' => $user->id
            ]);
            $order->save();

            // Tạo các mục đơn hàng từ giỏ hàng
            foreach ($cart->items as $item) {
                $orderItem = new Order_items([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'description' => $item->product->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_percent' => $item->tax_percent,
                    'tax_amount' => $item->tax_amount,
                    'discount_percent' => $item->discount_percent,
                    'discount_amount' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                    'total' => $item->total,
                    'options' => json_encode($item->options)
                ]);
                $orderItem->save();
            }

            // Tạo hóa đơn
            $invoice = new Invoices([
                'invoice_number' => 'INV-' . time() . Str::random(5),
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'status' => 'draft',
                'subtotal' => $order->subtotal,
                'tax_amount' => $order->tax_amount,
                'discount_amount' => $order->discount_amount,
                'total_amount' => $order->total_amount,
                'due_date' => now()->addDays(7),
                'created_by' => $user->id
            ]);
            $invoice->save();

            // Tạo các mục hóa đơn
            foreach ($cart->items as $item) {
                $invoiceItem = new Invoice_items([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'description' => $item->product->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_percent' => $item->tax_percent,
                    'tax_amount' => $item->tax_amount,
                    'discount_percent' => $item->discount_percent,
                    'discount_amount' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                    'total' => $item->total
                ]);
                $invoiceItem->save();
            }

            // Xóa giỏ hàng sau khi tạo đơn hàng thành công
            CartItem::where('cart_id', $cart->id)->delete();
            $cart->delete();

            // Commit transaction
            DB::commit();

            // Gửi email thông báo đơn hàng mới cho khách hàng và admin
            // ...

            return response()->json([
                'status' => 'success',
                'message' => 'Đơn hàng đã được tạo thành công',
                'data' => [
                    'order' => $order,
                    'invoice' => $invoice,
                    'redirect_url' => route('payment.method', ['invoice' => $invoice->id])
                ]
            ]);

        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
