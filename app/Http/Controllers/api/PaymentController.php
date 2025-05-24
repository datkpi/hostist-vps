<?php

namespace App\Http\Controllers\api;


use App\Http\Controllers\Controller;
use App\Models\Invoices;
use App\Models\Payments;
use App\Models\Customers;
use App\Models\Products;
use App\Models\Config;
use App\Mail\PaymentConfirmation;
use App\Mail\PaymentConfirmationAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    // Chọn phương thức thanh toán
    public function chooseMethod(Request $request, $invoiceId)
    {
        $invoice = Invoices::with('customer')->findOrFail($invoiceId);
        $config = Config::current();

        // Kiểm tra quyền truy cập
        if (Auth::id() != $invoice->customer->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền truy cập hóa đơn này'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'invoice' => $invoice,
                'payment_methods' => [
                    'balance' => [
                        'name' => 'Số dư tài khoản',
                        'available' => $invoice->customer->balance >= $invoice->total_amount,
                        'balance' => $invoice->customer->balance
                    ],
                    'bank' => [
                        'name' => 'Chuyển khoản ngân hàng',
                        'available' => true,
                        'bank_info' => [
                            'bank_name' => $config->company_bank_name,
                            'account_number' => $config->company_bank_account_number,
                            'account_name' => $config->company_bank_account_name,
                            'branch' => $config->company_bank_branch,
                        ]
                    ],
                    'momo' => [
                        'name' => 'Ví MoMo',
                        'available' => !empty($config->momo_phone_number),
                        'momo_info' => [
                            'phone' => $config->momo_phone_number,
                            'account_name' => $config->momo_account_name,
                        ]
                    ],
                    'zalopay' => [
                        'name' => 'Ví ZaloPay',
                        'available' => !empty($config->zalopay_phone_number),
                        'zalopay_info' => [
                            'phone' => $config->zalopay_phone_number,
                            'account_name' => $config->zalopay_account_name,
                        ]
                    ]
                ]
            ]
        ]);
    }

    // Xử lý thanh toán
    public function processPayment(Request $request, $invoiceId)
    {
        $request->validate([
            'payment_method' => 'required|in:balance,bank,momo,zalopay',
            'agree_terms' => 'required|accepted'
        ]);

        $invoice = Invoices::with(['customer', 'order'])->findOrFail($invoiceId);

        // Kiểm tra quyền truy cập
        if (Auth::id() != $invoice->customer->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền truy cập hóa đơn này'
            ], 403);
        }

        // Kiểm tra hóa đơn đã thanh toán chưa
        if ($invoice->status == 'paid') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hóa đơn này đã được thanh toán'
            ], 400);
        }

        $config = Config::current();
        $customer = $invoice->customer;
        $user = Auth::user();

        // Tạo mã giao dịch
        $transactionCode = 'PAY' . time() . Str::random(5);

        try {
            // Xử lý theo phương thức thanh toán
            if ($request->payment_method == 'balance') {
                // Kiểm tra số dư
                if ($customer->balance < $invoice->total_amount) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Số dư tài khoản không đủ để thanh toán'
                    ], 400);
                }

                DB::beginTransaction();

                // Trừ tiền từ số dư
                $customer->updateBalance(-$invoice->total_amount);

                // Tạo thanh toán
                $payment = new Payments([
                    'order_id' => $invoice->order_id,
                    'invoice_id' => $invoice->id,
                    'payment_number' => $transactionCode,
                    'amount' => $invoice->total_amount,
                    'payment_method' => 'balance',
                    'payment_date' => now(),
                    'transaction_id' => $transactionCode,
                    'status' => 'completed',
                    'notes' => 'Thanh toán từ số dư tài khoản'
                ]);
                $payment->save();

                // Cập nhật trạng thái hóa đơn và đơn hàng
                $invoice->status = 'paid';
                $invoice->save();

                $order = $invoice->order;
                $order->status = 'processing';
                $order->save();

                // Tạo dịch vụ từ đơn hàng
                $this->createServices($order);

                DB::commit();

                // Gửi email xác nhận thanh toán
                // ...

                return response()->json([
                    'status' => 'success',
                    'message' => 'Thanh toán thành công',
                    'data' => [
                        'payment' => $payment,
                        'invoice' => $invoice,
                        'redirect_url' => route('payment.success', ['code' => $transactionCode])
                    ]
                ]);

            } else {
                // Phương thức thanh toán khác (bank, momo, zalopay)
                // Chuẩn bị dữ liệu thanh toán
                $paymentDetails = [];

                switch ($request->payment_method) {
                    case 'bank':
                        $paymentDetails = [
                            'bank_name' => $config->company_bank_name,
                            'account_number' => $config->company_bank_account_number,
                            'account_name' => $config->company_bank_account_name,
                            'branch' => $config->company_bank_branch,
                        ];
                        break;

                    case 'momo':
                        $paymentDetails = [
                            'phone' => $config->momo_phone_number,
                            'account_name' => $config->momo_account_name,
                        ];
                        break;

                    case 'zalopay':
                        $paymentDetails = [
                            'phone' => $config->zalopay_phone_number,
                            'account_name' => $config->zalopay_account_name,
                        ];
                        break;
                }

                // Chuẩn bị dữ liệu thanh toán
                $paymentData = [
                    'transaction_code' => $transactionCode,
                    'amount' => $invoice->total_amount,
                    'payment_method' => $request->payment_method,
                    'customer_name' => $user->name,
                    'customer_email' => $user->email,
                    'customer_id' => $customer->id,
                    'date' => now()->format('d/m/Y H:i:s'),
                    'invoice_number' => $invoice->invoice_number,
                    'note_format' => $config->deposit_note_format ?
                        str_replace('{customer_id}', $customer->id, $config->deposit_note_format) :
                        "TT{$invoice->invoice_number}",
                ];

                // Thêm thông tin chi tiết theo phương thức
                switch ($request->payment_method) {
                    case 'bank':
                        $paymentData['bank_info'] = $paymentDetails;
                        if ($config->company_bank_qr_code) {
                            $paymentData['qr_code_url'] = url('storage/' . $config->company_bank_qr_code);
                        }
                        break;

                    case 'momo':
                        $paymentData['momo_info'] = $paymentDetails;
                        if ($config->momo_qr_code) {
                            $paymentData['qr_code_url'] = url('storage/' . $config->momo_qr_code);
                        }
                        break;

                    case 'zalopay':
                        $paymentData['zalopay_info'] = $paymentDetails;
                        if ($config->zalopay_qr_code) {
                            $paymentData['qr_code_url'] = url('storage/' . $config->zalopay_qr_code);
                        }
                        break;
                }

                // Tạo thanh toán đang chờ xử lý
                $payment = new Payments([
                    'order_id' => $invoice->order_id,
                    'invoice_id' => $invoice->id,
                    'payment_number' => $transactionCode,
                    'amount' => $invoice->total_amount,
                    'payment_method' => $request->payment_method,
                    'payment_date' => now(),
                    'transaction_id' => $transactionCode,
                    'status' => 'pending',
                    'notes' => 'Thanh toán qua ' . $this->getPaymentMethodName($request->payment_method)
                ]);
                $payment->save();

                // Cập nhật trạng thái hóa đơn
                $invoice->status = 'pending';
                $invoice->save();

                // Gửi email hướng dẫn thanh toán
                if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Mail::to($user->email)->send(new PaymentInstructionMail($paymentData));
                    } catch (\Exception $e) {
                        // Log lỗi
                        Log::error('Lỗi gửi email cho khách hàng: ' . $e->getMessage());
                    }
                }

                // Gửi email thông báo đơn hàng mới cho admin
                $adminEmail = config('mail.admin_email');
                if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                    try {
                        Mail::to($adminEmail)->send(new NewOrderAdminMail($paymentData));
                    } catch (\Exception $e) {
                        // Log lỗi
                        Log::error('Lỗi gửi email cho admin: ' . $e->getMessage());
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Vui lòng hoàn tất thanh toán theo hướng dẫn',
                    'data' => [
                        'payment' => $payment,
                        'payment_details' => $paymentData,
                        'redirect_url' => route('payment.instruction', ['code' => $transactionCode])
                    ]
                ]);
            }

        } catch (\Exception $e) {
            // Rollback nếu có lỗi
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Xác nhận thanh toán (cho admin)
    public function confirmPayment(Request $request, $paymentId)
    {
        // Kiểm tra quyền admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ], 403);
        }

        $payment = Payments::with(['invoice', 'order'])->findOrFail($paymentId);

        // Kiểm tra trạng thái hiện tại
        if ($payment->status != 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Thanh toán này không ở trạng thái chờ xác nhận'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Cập nhật trạng thái thanh toán
            $payment->status = 'completed';
            $payment->save();

            // Cập nhật trạng thái hóa đơn
            $invoice = $payment->invoice;
            $invoice->status = 'paid';
            $invoice->save();

            // Cập nhật trạng thái đơn hàng
            $order = $payment->order;
            $order->status = 'processing';
            $order->save();

            // Tạo dịch vụ từ đơn hàng
            $this->createServices($order);

            DB::commit();

            // Gửi email xác nhận thanh toán cho khách hàng
            $customer = $invoice->customer;
            $user = $customer->user;

            if ($user && $user->email) {
                try {
                    Mail::to($user->email)->send(new PaymentConfirmation([
                        'payment' => $payment,
                        'invoice' => $invoice,
                        'customer' => $customer,
                        'user' => $user
                    ]));
                } catch (\Exception $e) {
                    // Log lỗi
                    Log::error('Lỗi gửi email xác nhận thanh toán: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Xác nhận thanh toán thành công',
                'data' => [
                    'payment' => $payment,
                    'invoice' => $invoice,
                    'order' => $order
                ]
            ]);

        } catch (\Exception $e) {
            // Rollback nếu có lỗi
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Hủy thanh toán (cho admin)
    public function cancelPayment(Request $request, $paymentId)
    {
        // Kiểm tra quyền admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn không có quyền thực hiện hành động này'
            ], 403);
        }

        $payment = Payments::with(['invoice', 'order'])->findOrFail($paymentId);

        // Kiểm tra trạng thái hiện tại
        if ($payment->status != 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Thanh toán này không ở trạng thái chờ xác nhận'
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Cập nhật trạng thái thanh toán
            $payment->status = 'failed';
            $payment->notes = 'Bị hủy bởi admin: ' . $request->reason;
            $payment->save();

            // Không thay đổi trạng thái hóa đơn và đơn hàng
            // để khách hàng có thể thử thanh toán lại

            DB::commit();

            // Gửi email thông báo hủy thanh toán cho khách hàng
            $invoice = $payment->invoice;
            $customer = $invoice->customer;
            $user = $customer->user;

            if ($user && $user->email) {
                try {
                    Mail::to($user->email)->send(new PaymentCancelled([
                        'payment' => $payment,
                        'invoice' => $invoice,
                        'customer' => $customer,
                        'user' => $user,
                        'reason' => $request->reason
                    ]));
                } catch (\Exception $e) {
                    // Log lỗi
                    Log::error('Lỗi gửi email hủy thanh toán: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đã hủy thanh toán',
                'data' => [
                    'payment' => $payment,
                    'invoice' => $invoice
                ]
            ]);

        } catch (\Exception $e) {
            // Rollback nếu có lỗi
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'Đã có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Phương thức trợ giúp để tạo dịch vụ từ đơn hàng
    private function createServices($order)
    {
        $orderItems = Order_items::where('order_id', $order->id)->get();

        foreach ($orderItems as $item) {
            $product = Products::find($item->product_id);

            if (!$product) {
                continue;
            }

            // Phân tích các tùy chọn
            $options = json_decode($item->options, true) ?: [];
            $period = $options['period'] ?? 1; // Mặc định 1 năm

            // Tạo bản sao sản phẩm làm dịch vụ của khách hàng
            $service = new Products([
                'category_id' => $product->category_id,
                'customer_id' => $order->customer_id,
                'parent_product_id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->image,
                'type' => $product->type,
                'product_status' => 'sold',
                'service_status' => 'active',
                'start_date' => now(),
                'end_date' => now()->addYears($period),
                'next_due_date' => now()->addYears($period),
                'is_recurring' => $product->is_recurring,
                'recurring_period' => $period * 12, // Chuyển đổi năm thành tháng
                'auto_renew' => $options['auto_renew'] ?? false,
                'meta_data' => $product->meta_data,
                'options' => json_encode($options)
            ]);

            $service->save();
        }
    }

    // Lấy tên phương thức thanh toán
    private function getPaymentMethodName($method)
    {
        $methods = [
            'balance' => 'Số dư tài khoản',
            'bank' => 'Chuyển khoản ngân hàng',
            'momo' => 'Ví MoMo',
            'zalopay' => 'Ví ZaloPay'
        ];

        return $methods[$method] ?? $method;
    }
}
