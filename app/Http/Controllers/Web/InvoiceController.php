<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Config;
use App\Models\User;
use App\Models\Invoices;
use App\Models\Order_items;
use App\Models\Orders;
use App\Models\Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Hiển thị trang tạo báo giá từ giỏ hàng
     */
    public function showQuote(Request $request)
    {
        // Lấy giỏ hàng hiện tại
        $cart = $this->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống, vui lòng thêm sản phẩm trước khi tạo báo giá');
        }

        // Lấy thông tin người dùng
        $user = Auth::user();

        // Tạo số báo giá duy nhất
        $quoteNumber = 'QUOTE-' . time() . Str::random(5);

        // Lấy thông tin công ty
        $config = Config::current();

        // Tạo ngày báo giá và ngày hết hạn
        $quoteDate = Carbon::now()->format('d/m/Y');
        $expireDate = Carbon::now()->addDays(7)->format('d/m/Y');

        // Tính tổng tiền
        $subtotal = $cart->subtotal;
        $vatRate = 0; // Đã bỏ VAT
        $vatAmount = 0;
        $total = $subtotal;

        return view('source.web.invoice.quote', compact(
            'cart',
            'user',
            'quoteNumber',
            'quoteDate',
            'expireDate',
            'config',
            'subtotal',
            'vatRate',
            'vatAmount',
            'total'
        ));
    }

    /**
     * Lấy giỏ hàng hiện tại
     */
    private function getCart()
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->with('items.product')->first();
        } else {
            $sessionId = session()->getId();
            $cart = Cart::where('session_id', $sessionId)->with('items.product')->first();
        }

        return $cart;
    }

    /**
     * Tải PDF báo giá
     */
    public function downloadPdf(Request $request, $id = null)
    {
        // Nếu có ID, lấy hóa đơn cụ thể; nếu không, lấy từ giỏ hàng hiện tại
        if ($id) {
            $invoice = Invoices::with(['order.items.product', 'order.customer'])->findOrFail($id);

            // Kiểm tra quyền truy cập
            if (Auth::user()->customer->id != $invoice->order->customer_id) {
                return redirect()->route('customer.invoices')
                    ->with('error', 'Bạn không có quyền truy cập hóa đơn này');
            }

            // Thiết lập dữ liệu cho PDF
            $user = Auth::user();
            $config = Config::current();
            $quoteNumber = $invoice->invoice_number;
            $quoteDate = $invoice->created_at->format('d/m/Y');
            $expireDate = $invoice->due_date ? $invoice->due_date->format('d/m/Y') : Carbon::now()->addDays(7)->format('d/m/Y');
            $subtotal = $invoice->order->subtotal;
            $vatRate = 0; // Không tính VAT
            $vatAmount = 0;
            $total = $invoice->order->total_amount;

            // Dữ liệu cho view PDF
            $data = compact(
                'invoice',
                'user',
                'config',
                'quoteNumber',
                'quoteDate',
                'expireDate',
                'subtotal',
                'vatRate',
                'vatAmount',
                'total'
            );

            // Chuẩn bị QR code
            if ($config && $config->company_bank_qr_code) {
                $qrPath = storage_path('app/public/' . $config->company_bank_qr_code);
                if (file_exists($qrPath)) {
                    $data['qrBase64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($qrPath));
                }
            }

            // Tạo PDF
            $pdf = PDF::loadView('source.web.invoice.pdf', $data);

            // Tạo tên file
            $fileName = 'hoa-don-' . $invoice->invoice_number . '.pdf';
        } else {
            // Lấy từ giỏ hàng hiện tại (cho báo giá mới)
            $cart = $this->getCart();

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Giỏ hàng trống, vui lòng thêm sản phẩm trước khi tạo báo giá');
            }

            // Lấy thông tin người dùng
            $user = Auth::user();

            // Tạo số báo giá duy nhất
            $quoteNumber = 'QUOTE-' . time() . Str::random(5);

            // Lấy thông tin công ty
            $config = Config::current();

            // Tạo ngày báo giá và ngày hết hạn
            $quoteDate = Carbon::now()->format('d/m/Y');
            $expireDate = Carbon::now()->addDays(7)->format('d/m/Y');

            // Tính tổng tiền
            $subtotal = $cart->subtotal;
            $vatRate = 0; // Không tính VAT
            $vatAmount = 0;
            $total = $subtotal;

            // Dữ liệu cho view PDF
            $data = compact(
                'cart',
                'user',
                'config',
                'quoteNumber',
                'quoteDate',
                'expireDate',
                'subtotal',
                'vatRate',
                'vatAmount',
                'total'
            );

            // Chuẩn bị QR code
            if ($config && $config->company_bank_qr_code) {
                $qrPath = storage_path('app/public/' . $config->company_bank_qr_code);
                if (file_exists($qrPath)) {
                    $data['qrBase64'] = 'data:image/png;base64,' . base64_encode(file_get_contents($qrPath));
                }
            }

            // Tạo PDF
            $pdf = PDF::loadView('source.web.invoice.pdf', $data);

            // Tạo tên file
            $fileName = 'bao-gia-' . date('Ymd') . '.pdf';
        }

        // Cấu hình PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        // Tải xuống file
        return $pdf->download($fileName);
    }

    /**
     * Gửi báo giá qua email
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string'
        ]);

        // Dùng cách đơn giản để gửi email HTML trực tiếp
        $email = $request->input('email');
        $message = $request->input('message', '');

        try {
            // Lấy giỏ hàng hiện tại
            $cart = $this->getCart();

            if (!$cart || $cart->items->isEmpty()) {
                return redirect()->route('cart.index')
                    ->with('error', 'Giỏ hàng trống, vui lòng thêm sản phẩm trước khi gửi báo giá');
            }

            // Lấy thông tin người dùng và công ty
            $user = Auth::user();
            $config = Config::current();
            $quoteNumber = 'QUOTE-' . time() . Str::random(5);

            // Tạo danh sách sản phẩm HTML
            $productsHtml = '';
            foreach ($cart->items as $item) {
                $options = json_decode($item->options, true) ?: [];
                $period = $options['period'] ?? 1;
                $productName = $item->product->name ?? 'Sản phẩm';
                $productSubtotal = number_format($item->subtotal, 0, ',', '.') . ' đ';

                $productsHtml .= "
                    <tr>
                        <td>{$period} năm {$productName}</td>
                        <td>{$productSubtotal}</td>
                    </tr>
                ";
            }

            // Tạo email content
            $emailContent = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                        .header h1 { margin: 0; color: #333; font-size: 24px; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
                        th { font-weight: bold; }
                        .footer { margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; font-size: 12px; color: #777; text-align: center; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>" . ($config->company_name ?? 'Hostist company') . "</h1>
                            <p>Báo giá #{$quoteNumber}</p>
                        </div>

                        <p>Kính gửi {$user->name},</p>

                        <p>Cảm ơn bạn đã quan tâm đến dịch vụ của chúng tôi. Chúng tôi gửi đến bạn báo giá theo yêu cầu.</p>";

            // Thêm lời nhắn nếu có
            if (!empty($message)) {
                $emailContent .= "
                        <div style='padding: 15px; background-color: #f5f5f5; border-left: 4px solid #007bff; margin-bottom: 20px;'>
                            <p><strong>Lời nhắn:</strong></p>
                            <p>{$message}</p>
                        </div>";
            }

            $emailContent .= "
                        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                            <p><strong>Ngày tạo báo giá:</strong> " . date('d/m/Y') . "</p>
                            <p><strong>Ngày hết hạn:</strong> " . Carbon::now()->addDays(7)->format('d/m/Y') . "</p>
                            <p><strong>Mã báo giá:</strong> {$quoteNumber}</p>
                        </div>

                        <div>
                            <h3>Thông tin báo giá</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {$productsHtml}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Tổng cộng</th>
                                        <th>" . number_format($cart->subtotal, 0, ',', '.') . " đ</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <p>Vui lòng kiểm tra file PDF đính kèm để xem chi tiết báo giá.</p>

                        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email " .
                ($config->support_email ?? 'supposthostit@gmail.com') . " hoặc số điện thoại " .
                ($config->support_phone ?? 'N/A') . ".</p>

                        <p>Trân trọng,<br>
                        " . ($config->company_name ?? 'Hostist company') . "</p>

                        <div class='footer'>
                            <p>© " . date('Y') . " " . ($config->company_name ?? 'Hostist company') . ". Tất cả các quyền được bảo lưu.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Tạo PDF để đính kèm
            $pdf = PDF::loadView('source.web.invoice.pdf', [
                'cart' => $cart,
                'user' => $user,
                'config' => $config,
                'quoteNumber' => $quoteNumber,
                'quoteDate' => date('d/m/Y'),
                'expireDate' => Carbon::now()->addDays(7)->format('d/m/Y'),
                'subtotal' => $cart->subtotal,
                'vatRate' => 0,
                'vatAmount' => 0,
                'total' => $cart->subtotal
            ]);

            // Cấu hình PDF
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            // Gửi email với nội dung HTML trực tiếp
            Mail::html($emailContent, function ($mail) use ($email, $quoteNumber, $config, $pdf) {
                $mail->to($email)
                    ->subject('Báo giá #' . $quoteNumber . ' - ' . ($config->company_name ?? 'Công ty chúng tôi'))
                    ->attachData($pdf->output(), 'bao-gia-' . date('Ymd') . '.pdf');
            });

            return redirect()->back()->with('success', 'Đã gửi báo giá qua email thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi khi gửi email: ' . $e->getMessage());
        }
    }

    /**
     * Tiến hành thanh toán
     */
    public function proceedToPayment(Request $request, $invoiceId = null)
    {
        // Lấy thông tin hóa đơn từ ID hoặc từ giỏ hàng hiện tại
        if ($invoiceId) {
            $invoice = Invoices::with(['order', 'order.items.product'])->findOrFail($invoiceId);
        } else {
            // Tạo hóa đơn mới từ giỏ hàng
            $result = $this->createInvoiceFromCart();
            if (!$result['success']) {
                return redirect()->route('cart.index')->with('error', $result['message']);
            }
            $invoice = $result['invoice'];
        }

        // Lấy thông tin user hiện tại
        $user = Auth::user();
        $customer = $user->customer;

        // Kiểm tra quyền truy cập (bảo đảm user chỉ thanh toán hóa đơn của mình)
        if ($customer->id != $invoice->customer_id) {
            return redirect()->route('customer.invoices')
                ->with('error', 'Bạn không có quyền thực hiện thanh toán cho hóa đơn này');
        }

        // Kiểm tra trạng thái hóa đơn
        if ($invoice->status == 'paid') {
            return redirect()->route('customer.orders')
                ->with('info', 'Hóa đơn này đã được thanh toán. Bạn có thể xem chi tiết đơn hàng.');
        }

        // Số tiền cần thanh toán
        $amountToPay = $invoice->total_amount;

        // Xử lý thông tin domain cho các sản phẩm trong đơn hàng
        foreach ($invoice->order->items as $item) {
            $options = json_decode($item->options, true) ?: [];
            $item->period = $options['period'] ?? $item->duration ?? 1;
            $item->domain = $item->domain ?? ($options['domain'] ?? 'N/A');
            $item->isSSLorDomain = $item->product && ($item->product->type == 'ssl' || $item->product->type == 'domain');
        }

        // Kiểm tra số dư tài khoản
        if ($customer->hasBalance($amountToPay)) {
            // Đủ tiền - Thực hiện thanh toán từ ví
            DB::beginTransaction();
            try {
                // Trừ tiền từ tài khoản khách hàng
                $customer->updateBalance(-$amountToPay);

                // Cập nhật trạng thái hóa đơn
                $invoice->status = 'paid';
                $invoice->save();

                // Cập nhật trạng thái đơn hàng
                $order = $invoice->order;
                $order->status = 'completed'; // Chuyển sang trạng thái đang xử lý
                $order->save();

                // Tạo bản ghi thanh toán
                $payment = new Payments([
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                    'payment_number' => 'PAY-' . time() . Str::random(5),
                    'amount' => $amountToPay,
                    'payment_method' => 'wallet', // Thanh toán từ ví
                    'payment_date' => now(),
                    'transaction_id' => 'WALLET-' . time() . Str::random(5),
                    'status' => 'completed',
                    'notes' => 'Thanh toán từ số dư tài khoản'
                ]);
                $payment->save();

                // Xóa giỏ hàng sau khi thanh toán thành công
                $this->clearCart();

                // Commit transaction
                DB::commit();

                // Gửi email thông báo thanh toán thành công (nếu cần)
                try {
                    $this->sendPaymentSuccessEmail($user, $invoice, $payment);
                } catch (\Exception $e) {
                    Log::error('Lỗi gửi email xác nhận thanh toán: ' . $e->getMessage());
                }

                return redirect()->route('customer.orders')
                    ->with('success', 'Thanh toán thành công! Đơn hàng của bạn đang được xử lý.');
            } catch (\Exception $e) {
                // Rollback nếu có lỗi
                DB::rollback();

                // Log lỗi
                Log::error('Lỗi thanh toán: ' . $e->getMessage());

                return redirect()->back()
                    ->with('error', 'Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại sau.');
            }
        } else {
            // Không đủ tiền - Tạo thanh toán chuyển khoản ngân hàng
            DB::beginTransaction();
            try {
                // Tạo mã giao dịch
                $transactionCode = 'PAY' . time() . Str::random(5);

                // Lấy thông tin cấu hình
                $config = Config::current();

                // Chuẩn bị dữ liệu chi tiết thanh toán
                $paymentDetails = [
                    'bank_name' => $config->company_bank_name,
                    'account_number' => $config->company_bank_account_number,
                    'account_name' => $config->company_bank_account_name,
                    'branch' => $config->company_bank_branch,
                    'qr_code' => $config->company_bank_qr_code,
                ];

                // Tạo bản ghi thanh toán với trạng thái chờ
                $payment = new Payments([
                    'order_id' => $invoice->order->id,
                    'invoice_id' => $invoice->id,
                    'payment_number' => 'PAY-' . $transactionCode,
                    'amount' => $amountToPay,
                    'payment_method' => 'bank',
                    'payment_date' => now(),
                    'transaction_id' => $transactionCode,
                    'status' => 'pending', // Trạng thái chờ xác nhận
                    'notes' => 'Chờ xác nhận thanh toán chuyển khoản ngân hàng',
                    'payment_details' => $paymentDetails,
                ]);
                $payment->save();

                // Cập nhật trạng thái hóa đơn nếu chưa là "sent"
                if ($invoice->status != 'sent') {
                    $invoice->status = 'sent';
                    $invoice->save();
                }

                // Xóa giỏ hàng sau khi tạo đơn hàng thành công
                $this->clearCart();

                // Gửi email thông báo cho admin nếu cần
                try {
                    $adminEmail = config('mail.admin_email');
                    if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                        $this->sendAdminPaymentNotification($adminEmail, $user, $invoice, $payment);
                    }
                } catch (\Exception $e) {
                    Log::error('Lỗi gửi email cho admin: ' . $e->getMessage());
                }

                // Commit transaction
                DB::commit();

                // Hiển thị trang thông tin chuyển khoản
                return view('source.web.payment.bank_transfer', [
                    'invoice' => $invoice,
                    'payment' => $payment,
                    'config' => $config,
                    'transactionCode' => $transactionCode,
                    'user' => $user,
                    'amountToPay' => $amountToPay,
                ]);
            } catch (\Exception $e) {
                // Rollback nếu có lỗi
                DB::rollback();

                // Log lỗi
                Log::error('Lỗi tạo yêu cầu thanh toán: ' . $e->getMessage());

                return redirect()->back()
                    ->with('error', 'Có lỗi xảy ra khi tạo yêu cầu thanh toán. Vui lòng thử lại sau.');
            }
        }
    }

    /**
     * Gửi email thông báo thanh toán thành công
     */
    private function sendPaymentSuccessEmail($user, $invoice, $payment)
    {
        // Lấy thông tin cấu hình
        $config = Config::current();

        // Tạo bảng thông tin dịch vụ và domain
        $servicesHtml = '';
        foreach ($invoice->order->items as $item) {
            $options = json_decode($item->options, true) ?: [];
            $period = $options['period'] ?? $item->duration ?? 1;
            $domain = $item->domain ?? ($options['domain'] ?? 'N/A');

            // Kiểm tra xem item có phải là SSL hoặc domain không
            $isSSLorDomain = $item->product && ($item->product->type == 'ssl' || $item->product->type == 'domain');

            // Tạo highlight cho domain nếu là dịch vụ SSL hoặc domain
            $domainCell = $isSSLorDomain && $domain != 'N/A'
                ? "<strong style='color: #0d6efd;'>{$domain}</strong>"
                : "-";

            $subtotal = number_format($item->subtotal, 0, ',', '.') . ' đ';

            $servicesHtml .= "
        <tr>
            <td>{$item->name}</td>
            <td>{$period} năm</td>
            <td>{$domainCell}</td>
            <td style='text-align: right;'>{$subtotal}</td>
        </tr>";
        }

        // Tạo nội dung email
        $emailContent = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #333; font-size: 24px; }
        .success-box { background-color: #d4edda; border-color: #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: bold; }
        .footer { margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; font-size: 12px; color: #777; text-align: center; }
        .services-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .services-table th, .services-table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        .services-table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>" . ($config->company_name ?? 'Hostist company') . "</h1>
            <p>Xác nhận thanh toán</p>
        </div>

        <div class='success-box'>
            <p><strong>Thanh toán thành công!</strong> Cảm ơn bạn đã thanh toán.</p>
        </div>

        <p>Kính gửi {$user->name},</p>

        <p>Chúng tôi xác nhận đã nhận được thanh toán của bạn với thông tin như sau:</p>

        <table>
            <tr>
                <th>Mã hóa đơn:</th>
                <td>{$invoice->invoice_number}</td>
            </tr>
            <tr>
                <th>Mã đơn hàng:</th>
                <td>{$invoice->order->order_number}</td>
            </tr>
            <tr>
                <th>Số tiền:</th>
                <td>" . number_format($payment->amount, 0, ',', '.') . " đ</td>
            </tr>
            <tr>
                <th>Phương thức:</th>
                <td>Thanh toán từ số dư tài khoản</td>
            </tr>
            <tr>
                <th>Ngày thanh toán:</th>
                <td>{$payment->payment_date->format('d/m/Y H:i:s')}</td>
            </tr>
            <tr>
                <th>Mã giao dịch:</th>
                <td>{$payment->transaction_id}</td>
            </tr>
        </table>

        <p>Chi tiết dịch vụ:</p>
        <table class='services-table'>
            <thead>
                <tr>
                    <th>Dịch vụ</th>
                    <th>Thời hạn</th>
                    <th>Domain</th>
                    <th style='text-align: right;'>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                {$servicesHtml}
            </tbody>
            <tfoot>
                <tr>
                    <th colspan='3' style='text-align: right;'>Tổng cộng:</th>
                    <th style='text-align: right;'>" . number_format($invoice->total_amount, 0, ',', '.') . " đ</th>
                </tr>
            </tfoot>
        </table>

        <p>Đơn hàng của bạn đang được xử lý. Bạn có thể theo dõi tình trạng đơn hàng tại
        <a href='" . route('customer.orders') . "'>Trang quản lý đơn hàng</a>.</p>

        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email " .
            ($config->support_email ?? 'supposthostit@gmail.com') . " hoặc số điện thoại " .
            ($config->support_phone ?? 'N/A') . ".</p>

        <p>Trân trọng,<br>
        " . ($config->company_name ?? 'Hostist company') . "</p>

        <div class='footer'>
            <p>© " . date('Y') . " " . ($config->company_name ?? 'Hostist company') . ". Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
";

        // Gửi email
        Mail::html($emailContent, function ($mail) use ($user, $invoice) {
            $mail->to($user->email)
                ->subject('Xác nhận thanh toán hóa đơn #' . $invoice->invoice_number);
        });
    }

    /**
     * Gửi thông báo cho admin về yêu cầu thanh toán mới
     */
    private function sendAdminPaymentNotification($adminEmail, $user, $invoice, $payment)
    {
        // Lấy thông tin cấu hình
        $config = Config::current();

        // Tạo bảng thông tin dịch vụ và domain
        $servicesHtml = '';
        foreach ($invoice->order->items as $item) {
            $options = json_decode($item->options, true) ?: [];
            $period = $options['period'] ?? $item->duration ?? 1;
            $domain = $item->domain ?? ($options['domain'] ?? 'N/A');

            // Kiểm tra xem item có phải là SSL hoặc domain không
            $isSSLorDomain = $item->product && ($item->product->type == 'ssl' || $item->product->type == 'domain');

            // Tạo highlight cho domain nếu là dịch vụ SSL hoặc domain
            $domainCell = $isSSLorDomain && $domain != 'N/A'
                ? "<strong style='color: #0d6efd;'>{$domain}</strong>"
                : "-";

            $subtotal = number_format($item->subtotal, 0, ',', '.') . ' đ';

            $servicesHtml .= "
        <tr>
            <td>{$item->name}</td>
            <td>{$period} năm</td>
            <td>{$domainCell}</td>
            <td>{$subtotal}</td>
        </tr>";
        }

        // Tạo nội dung email
        $emailContent = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #333; font-size: 24px; }
        .info-box { background-color: #cce5ff; border-color: #b8daff; color: #004085; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-weight: bold; width: 40%; }
        .footer { margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; font-size: 12px; color: #777; text-align: center; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; }
        .services-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .services-table th, .services-table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        .services-table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>" . ($config->company_name ?? 'Hostist company') . "</h1>
            <p>Thông báo yêu cầu thanh toán mới</p>
        </div>

        <div class='info-box'>
            <p><strong>Có yêu cầu thanh toán mới!</strong> Khách hàng đã chọn phương thức chuyển khoản ngân hàng.</p>
        </div>

        <p>Thông tin khách hàng:</p>
        <table>
            <tr>
                <th>Tên khách hàng:</th>
                <td>{$user->name}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{$user->email}</td>
            </tr>
            <tr>
                <th>ID khách hàng:</th>
                <td>{$user->customer->id}</td>
            </tr>
        </table>

        <p>Thông tin thanh toán:</p>
        <table>
            <tr>
                <th>Mã hóa đơn:</th>
                <td>{$invoice->invoice_number}</td>
            </tr>
            <tr>
                <th>Mã đơn hàng:</th>
                <td>{$invoice->order->order_number}</td>
            </tr>
            <tr>
                <th>Số tiền thanh toán:</th>
                <td>" . number_format($payment->amount, 0, ',', '.') . " đ</td>
            </tr>
            <tr>
                <th>Mã giao dịch:</th>
                <td>{$payment->transaction_id}</td>
            </tr>
            <tr>
                <th>Phương thức thanh toán:</th>
                <td>Chuyển khoản ngân hàng</td>
            </tr>
            <tr>
                <th>Ngày tạo yêu cầu:</th>
                <td>{$payment->payment_date->format('d/m/Y H:i:s')}</td>
            </tr>
            <tr>
                <th>Nội dung thanh toán:</th>
                <td>ThanhToan{$invoice->invoice_number}</td>
            </tr>
        </table>

        <p>Chi tiết dịch vụ:</p>
        <table class='services-table'>
            <thead>
                <tr>
                    <th>Dịch vụ</th>
                    <th>Thời hạn</th>
                    <th>Domain</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                {$servicesHtml}
            </tbody>
            <tfoot>
                <tr>
                    <th colspan='3' style='text-align: right;'>Tổng cộng:</th>
                    <th>" . number_format($invoice->total_amount, 0, ',', '.') . " đ</th>
                </tr>
            </tfoot>
        </table>

        <p>Sau khi nhận được thanh toán từ khách hàng, vui lòng truy cập trang quản trị để xác nhận thanh toán:</p>

        <div style='text-align: center; margin: 20px 0;'>
            <a href='" . route('admin.payments.index') . "' class='btn'>Xem danh sách thanh toán</a>
        </div>

        <div class='footer'>
            <p>© " . date('Y') . " " . ($config->company_name ?? 'Hostist company') . ". Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
";

        // Gửi email
        Mail::html($emailContent, function ($mail) use ($adminEmail, $payment) {
            $mail->to($adminEmail)
                ->subject('Yêu cầu thanh toán mới #' . $payment->transaction_id);
        });
    }

    /**
     * Tạo hoá đơn từ giỏ hàng hiện tại
     */
    private function createInvoiceFromCart()
    {
        // Lấy giỏ hàng hiện tại
        $cart = $this->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Giỏ hàng trống, vui lòng thêm sản phẩm trước khi thanh toán'
            ];
        }

        // Lấy thông tin người dùng
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return [
                'success' => false,
                'message' => 'Vui lòng cập nhật thông tin khách hàng trước khi thanh toán'
            ];
        }

        try {
            DB::beginTransaction();

            // Tạo đơn hàng
            $order = new Orders([
                'order_number' => 'ORD-' . time() . \Illuminate\Support\Str::random(5),
                'customer_id' => $customer->id,
                'status' => 'pending',
                'subtotal' => $cart->subtotal,
                'tax_amount' => 0, // Không tính thuế
                'discount_amount' => 0, // Không có giảm giá
                'total_amount' => $cart->subtotal,
                'notes' => 'Đơn hàng từ giỏ hàng',
                'created_by' => $user->id,
            ]);
            $order->save();

            // Tạo các mục đơn hàng
            foreach ($cart->items as $item) {
                // Tính toán đơn giá từ subtotal và quantity nếu price không có sẵn
                $itemPrice = isset($item->price) ? $item->price : ($item->subtotal / $item->quantity);

                // Parse options để lấy thông tin period và domain
                $options = json_decode($item->options, true) ?: [];
                $period = $options['period'] ?? 1;
                $domain = $options['domain'] ?? null;

                // Tạo order item
                $orderItem = new Order_items();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item->product_id;
                $orderItem->name = $item->product->name;
                $orderItem->quantity = $item->quantity;
                $orderItem->price = $itemPrice;
                $orderItem->options = $item->options; // Lưu toàn bộ options bao gồm domain
                $orderItem->subtotal = $item->subtotal;
                $orderItem->total = $item->subtotal;
                $orderItem->tax_percent = 0;
                $orderItem->tax_amount = 0;
                $orderItem->discount_percent = 0;
                $orderItem->discount_amount = 0;
                $orderItem->duration = $period;
                $orderItem->sku = $item->product->sku ?? '';

                // Nếu bạn đã thêm trường domain riêng vào bảng order_items
                if ($domain && $item->product && ($item->product->type == 'ssl' || $item->product->type == 'domain')) {
                    $orderItem->domain = $domain;
                }

                $orderItem->save();
            }

            // Tạo hoá đơn
            $invoice = new Invoices([
                'invoice_number' => 'INV-' . time() . \Illuminate\Support\Str::random(5),
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'status' => 'draft',
                'subtotal' => $order->subtotal,
                'tax_amount' => 0, // Không tính thuế
                'discount_amount' => 0, // Không có giảm giá
                'total_amount' => $order->total_amount,
                'due_date' => \Carbon\Carbon::now()->addDays(7)->format('Y-m-d'),
                'notes' => 'Hoá đơn từ đơn hàng ' . $order->order_number,
                'created_by' => $user->id,
            ]);
            $invoice->save();

            DB::commit();

            return [
                'success' => true,
                'invoice' => $invoice,
                'order' => $order
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Lỗi tạo hoá đơn: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo hoá đơn. Vui lòng thử lại sau.'
            ];
        }
    }
    /**
     * Xóa giỏ hàng sau khi thanh toán
     */
    private function clearCart()
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                // Xóa các mục trong giỏ hàng
                CartItem::where('cart_id', $cart->id)->delete();
                // Xóa giỏ hàng
                $cart->delete();
            }
        }
    }
}
