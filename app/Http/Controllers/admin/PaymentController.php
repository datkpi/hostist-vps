<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payments;
use App\Models\Invoices;
use App\Models\Orders;
use App\Models\Customers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Config;

class PaymentController extends Controller
{
    /**
     * Hiển thị danh sách yêu cầu thanh toán
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $payments = Payments::with(['invoice', 'order.customer.user'])
            ->when($status, function ($query, $status) {
                if ($status !== 'all') {
                    return $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10);

        $counts = [
            'all' => Payments::count(),
            'pending' => Payments::where('status', 'pending')->count(),
            'completed' => Payments::where('status', 'completed')->count(),
            'failed' => Payments::where('status', 'failed')->count(),
        ];

        // Thêm dữ liệu thống kê
        $stats = [
            'today_payments' => Payments::whereDate('created_at', Carbon::today())
                ->where('status', 'completed')
                ->sum('amount'),
            'total_completed' => Payments::where('status', 'completed')->sum('amount'),
            'total_pending' => Payments::where('status', 'pending')->sum('amount'),
        ];

        return view('source.admin.payments.index', compact('payments', 'status', 'counts', 'stats'));
    }

    /**
     * Xác nhận thanh toán
     */
    public function approve(Request $request, $id)
    {
        $payment = Payments::with(['invoice', 'order.customer.user'])->findOrFail($id);

        // Kiểm tra trạng thái
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Thanh toán này đã được xử lý trước đó.');
        }

        // Cập nhật trạng thái thanh toán
        $payment->status = 'completed';
        $payment->save();

        // Cập nhật trạng thái hóa đơn
        $invoice = $payment->invoice;
        $invoice->status = 'paid';
        $invoice->save();

        // Cập nhật trạng thái đơn hàng
        $order = $payment->order;
        $order->status = 'completed'; // Thay 'processing' thành 'completed'
        $order->save();

        // Gửi email thông báo cho khách hàng (nếu cần)
        if ($order->customer && $order->customer->user && $order->customer->user->email) {
            try {
                $this->sendPaymentApprovedEmail($order->customer->user, $payment);
            } catch (\Exception $e) {
                Log::error('Lỗi gửi email xác nhận thanh toán: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.payments.index')
            ->with('success', 'Thanh toán đã được xác nhận và đơn hàng đã hoàn thành.');
    }

    /**
     * Từ chối thanh toán
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $payment = Payments::with(['invoice', 'order.customer.user'])->findOrFail($id);

        // Kiểm tra trạng thái
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Thanh toán này đã được xử lý trước đó.');
        }

        // Cập nhật trạng thái thanh toán
        $payment->status = 'failed';
        // Bỏ dòng gán verified_by và verified_at
        $payment->notes = 'Từ chối: ' . $request->reason;
        $payment->save();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Thanh toán đã bị từ chối.');
    }

    /**
     * Gửi email thông báo thanh toán đã được xác nhận
     */
    private function sendPaymentApprovedEmail($user, $payment)
    {
        // Lấy thông tin cấu hình
        $config = Config::current();

        // Lấy thời gian xác nhận, nếu không có thì dùng thời gian hiện tại
        $verifiedDate = $payment->verified_at ? $payment->verified_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s');

        // Tạo bảng chi tiết dịch vụ và thông tin SSL/Domain
        $servicesTable = '';
        $sslInfoHtml = '';

        if ($payment->order) {
            // Lấy order items
            $orderItems = \App\Models\Order_items::where('order_id', $payment->order->id)->get();

            if ($orderItems->count() > 0) {
                $servicesTable = '
            <h3>Chi tiết dịch vụ:</h3>
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Dịch vụ</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Thời hạn</th>
                        <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Domain</th>
                    </tr>
                </thead>
                <tbody>';

                foreach ($orderItems as $item) {
                    // Lấy thông tin domain từ nhiều nguồn
                    $domain = '';
                    $metaDataInfo = [];
                    $period = '';

                    // Lấy thông tin từ options
                    $options = json_decode($item->options, true) ?: [];
                    $period = $options['period'] ?? $item->duration ?? 1;

                    // Truy vấn thông tin sản phẩm để lấy meta_data
                    if ($item->product_id) {
                        $product = \App\Models\Products::find($item->product_id);
                        if ($product) {
                            // Lấy domain từ options hoặc trường domain
                            $domain = $item->domain ?? ($options['domain'] ?? '');

                            // Lấy metadata từ sản phẩm
                            if ($product->meta_data) {
                                // Kiểm tra xem meta_data đã là mảng hay còn là chuỗi JSON
                                $metaDataInfo = is_array($product->meta_data)
                                    ? $product->meta_data
                                    : (json_decode($product->meta_data, true) ?: []);

                                // Nếu không có domain từ các nguồn trước, thử lấy từ meta_data
                                if (empty($domain) && !empty($metaDataInfo['domain'])) {
                                    $domain = $metaDataInfo['domain'];
                                }

                                // Nếu sản phẩm là SSL hoặc domain, thêm thông tin chi tiết
                                if ($product->type == 'ssl' || $product->type == 'domain') {
                                    // Nếu có certificate trong meta_data, thêm thông tin vào
                                    if (!empty($metaDataInfo['certificate']) || !empty($metaDataInfo['expiration_date'])) {
                                        $sslInfoHtml .= '
                                    <div style="margin-top: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
                                        <h3>Thông tin SSL/Domain chi tiết</h3>';

                                        if (!empty($domain)) {
                                            $sslInfoHtml .= '<p><strong>Domain:</strong> ' . $domain . '</p>';
                                        }

                                        if (!empty($metaDataInfo['expiration_date'])) {
                                            $sslInfoHtml .= '<p><strong>Ngày hết hạn:</strong> ' . $metaDataInfo['expiration_date'] . '</p>';
                                        }

                                        // Thêm thông tin private key nếu có (chỉ hiển thị một phần)
                                        if (!empty($metaDataInfo['rsa_private_key'])) {
                                            $privateKeyPreview = substr($metaDataInfo['rsa_private_key'], 0, 100) . '...';
                                            $sslInfoHtml .= '
                                        <div style="margin-top: 10px;">
                                            <p><strong>Private Key:</strong></p>
                                            <pre style="background-color: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px; overflow: auto;">' . $privateKeyPreview . '</pre>
                                            <p><em>Private key đầy đủ được đính kèm trong email hoặc có thể truy cập qua trang quản lý của bạn.</em></p>
                                        </div>';
                                        }

                                        // Thêm thông tin certificate nếu có (chỉ hiển thị một phần)
                                        if (!empty($metaDataInfo['certificate'])) {
                                            $certPreview = substr($metaDataInfo['certificate'], 0, 100) . '...';
                                            $sslInfoHtml .= '
                                        <div style="margin-top: 10px;">
                                            <p><strong>Certificate:</strong></p>
                                            <pre style="background-color: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px; overflow: auto;">' . $certPreview . '</pre>
                                            <p><em>Certificate đầy đủ được đính kèm trong email hoặc có thể truy cập qua trang quản lý của bạn.</em></p>
                                        </div>';
                                        }

                                        $sslInfoHtml .= '</div>';
                                    }
                                }
                            }
                        }
                    }

                    $servicesTable .= '
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $item->name . '</td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $period . ' năm</td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">';

                    if (!empty($domain)) {
                        $servicesTable .= '<span style="display: inline-block; padding: 3px 6px; background-color: #17a2b8; color: white; border-radius: 3px;">' . $domain . '</span>';
                    } else {
                        $servicesTable .= '-';
                    }

                    $servicesTable .= '</td></tr>';
                }

                $servicesTable .= '</tbody></table>';
            }
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
        pre { white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>" . ($config->company_name ?? 'Hostist company') . "</h1>
            <p>Xác nhận thanh toán</p>
        </div>

        <div class='success-box'>
            <p><strong>Thanh toán của bạn đã được xác nhận!</strong> Cảm ơn bạn đã thanh toán.</p>
        </div>

        <p>Kính gửi {$user->name},</p>

        <p>Chúng tôi xác nhận đã nhận được thanh toán của bạn với thông tin như sau:</p>

        <table>
            <tr>
                <th>Mã hóa đơn:</th>
                <td>{$payment->invoice->invoice_number}</td>
            </tr>
            <tr>
                <th>Mã đơn hàng:</th>
                <td>{$payment->order->order_number}</td>
            </tr>
            <tr>
                <th>Số tiền:</th>
                <td>" . number_format($payment->amount, 0, ',', '.') . " đ</td>
            </tr>
            <tr>
                <th>Phương thức:</th>
                <td>Chuyển khoản ngân hàng</td>
            </tr>
            <tr>
                <th>Ngày xác nhận:</th>
                <td>{$verifiedDate}</td>
            </tr>
            <tr>
                <th>Mã giao dịch:</th>
                <td>{$payment->transaction_id}</td>
            </tr>
        </table>

        {$servicesTable}

        {$sslInfoHtml}

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

        // Tạo file certificate và private key để đính kèm nếu có
        $attachments = [];
        foreach ($orderItems as $item) {
            if ($item->product_id) {
                $product = \App\Models\Products::find($item->product_id);
                if ($product && $product->meta_data) {
                    $metaData = json_decode($product->meta_data, true) ?: [];

                    if (!empty($metaData['certificate'])) {
                        $certificateFileName = 'certificate-' . time() . '.crt';
                        $certificateContent = $metaData['certificate'];
                        $attachments[$certificateFileName] = $certificateContent;
                    }

                    if (!empty($metaData['rsa_private_key'])) {
                        $privateKeyFileName = 'private-key-' . time() . '.key';
                        $privateKeyContent = $metaData['rsa_private_key'];
                        $attachments[$privateKeyFileName] = $privateKeyContent;
                    }
                }
            }
        }

        // Gửi email với file đính kèm
        Mail::html($emailContent, function ($mail) use ($user, $payment, $attachments) {
            $mail->to($user->email)
                ->subject('Xác nhận thanh toán hóa đơn #' . $payment->invoice->invoice_number);

            // Đính kèm file certificate và private key
            foreach ($attachments as $fileName => $content) {
                $mail->attachData($content, $fileName);
            }
        });
    }
}
