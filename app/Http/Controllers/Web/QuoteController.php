<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\QuoteEmail;
use App\Models\Cart;
use App\Models\Config;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class QuoteController extends Controller
{
    /**
     * Tạo và tải xuống file PDF báo giá
     */
    public function downloadPdf()
    {
        // Lấy giỏ hàng hiện tại
        $cart = $this->getCart();

        // Nếu giỏ hàng trống, chuyển hướng về trang giỏ hàng
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi tạo báo giá.');
        }

        // Tạo tên file
        $fileName = 'bao-gia-' . date('Ymd') . '-' . $cart->id . '.pdf';

        // Tạo PDF và tải xuống
        return $this->generatePdf()->download($fileName);
    }

    /**
     * Gửi email báo giá với template đẹp
     */
    public function sendEmail(Request $request = null)
    {
        // Nếu gửi từ form, lấy email từ request, nếu không dùng email người dùng hiện tại
        $email = $request ? $request->input('email') : Auth::user()->email;
        $message = $request ? $request->input('message', '') : '';

        // Lấy giỏ hàng hiện tại
        $cart = $this->getCart();

        // Nếu giỏ hàng trống, chuyển hướng về trang giỏ hàng
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi gửi báo giá.');
        }

        $user = Auth::user();
        $config = Config::current();
        $quoteNumber = 'QUOTE-' . date('Ymd') . '-' . str_pad($cart->id, 4, '0', STR_PAD_LEFT);
        $quoteDate = Carbon::now()->format('d/m/Y');
        $expireDate = Carbon::now()->addDays(30)->format('d/m/Y'); // Tăng thành 30 ngày như mẫu
        $subtotal = $cart->subtotal;
        
        // Tính thuế và giảm giá
        $discount = $subtotal * 0; // Giảm giá 10%
        $afterDiscount = $subtotal - $discount;
        $vat = $afterDiscount * 0.10; // VAT 10%
        $total = $afterDiscount + $vat;
        
        $validity = '30 days';

        try {
            // Tạo PDF với template mới
            $pdf = $this->generateModernPdf();

            // Chuẩn bị dữ liệu cho template email đẹp
            $data = compact(
                'cart',
                'user',
                'config',
                'quoteNumber',
                'quoteDate',
                'expireDate',
                'subtotal',
                'discount',
                'afterDiscount',
                'vat',
                'total',
                'validity'
            );

            // Thêm thông tin domain cho items
            foreach ($cart->items as $item) {
                if ($item->product && ($item->product->type == 'ssl' || $item->product->type == 'domain')) {
                    $options = json_decode($item->options, true) ?: [];
                    $item->domain = $options['domain'] ?? 'N/A';
                }
            }

            // Tạo nội dung email với template đẹp
            $emailContent = $this->createBeautifulEmailTemplate($data, $message);

            // Gửi email
            Mail::html($emailContent, function ($message) use ($email, $quoteNumber, $config, $pdf) {
                $message->to($email)
                    ->subject('Báo giá #' . $quoteNumber . ' - ' . ($config->company_name ?? 'Công ty chúng tôi'))
                    ->attachData($pdf->output(), 'bao-gia-' . date('Ymd') . '.pdf');
            });

            return back()->with('success', 'Đã gửi báo giá qua email thành công.');

        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi gửi email: ' . $e->getMessage());
        }
    }

    /**
     * Tạo PDF với template hiện đại mới
     */
    private function generateModernPdf()
    {
        // Lấy giỏ hàng hiện tại
        $cart = $this->getCart();
        $user = Auth::user();
        $config = Config::current();

        // Tạo số báo giá
        $quoteNumber = 'QUOTE-' . date('Ymd') . '-' . str_pad($cart->id, 4, '0', STR_PAD_LEFT);
        $quoteDate = Carbon::now()->format('d/m/Y');
        $expireDate = Carbon::now()->addDays(30)->format('d/m/Y');
        
        $subtotal = $cart->subtotal;
        $discount = $subtotal * 0; // Giảm giá 10%
        $afterDiscount = $subtotal - $discount;
        $vat = $afterDiscount * 0.10; // VAT 10%
        $total = $afterDiscount + $vat;

        // Tạo HTML với template mới
        $html = $this->createModernPdfTemplate($cart, $user, $config, $quoteNumber, $quoteDate, $expireDate, $subtotal, $discount, $afterDiscount, $vat, $total);

        $pdf = PDF::loadHTML($html);

        // Thiết lập options cho PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 150,
            'defaultMediaType' => 'print',
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf;
    }

    /**
     * Tạo PDF với fallback
     */
    private function generatePdf()
    {
        try {
            return $this->generateModernPdf();
        } catch (\Exception $e) {
            // Fallback to simple PDF if modern one fails
            return $this->generateModernPdf();
        }
    }

    /**
     * Tạo template HTML hiện đại cho PDF
     */
    private function createModernPdfTemplate($cart, $user, $config, $quoteNumber, $quoteDate, $expireDate, $subtotal, $discount, $afterDiscount, $vat, $total)
    {
        // Tạo danh sách sản phẩm
        $productsHtml = '';
        foreach ($cart->items as $item) {
            $options = json_decode($item->options, true) ?: [];
            $period = $options['period'] ?? 1;
            $domain = $options['domain'] ?? 'N/A';
            $productName = $item->product->name ?? 'Sản phẩm';

            // Chi tiết sản phẩm dựa trên loại
            $productDetails = '';
            if ($item->product && $item->product->type == 'ssl') {
                $productDetails = "
                <div style='margin-top: 5px; color: #666; font-size: 9px; line-height: 1.5;'>
                    - Gói sản phẩm: 01 {$productName}<br>
                    - Tên miền sử dụng: " . ($domain !== 'N/A' ? "*.$domain" : 'N/A') . "<br>
                    - Mức độ xác minh: Xác minh tên miền<br><br>
                    <strong>Đã bao gồm:</strong><br>
                    - Tài khoản quản trị trực tiếp chứng thư số<br>
                    - Không giới hạn số lượng server cài đặt<br>
                    - Không giới hạn số lượng cấp khóa (keypair)<br>
                    - Hỗ trợ và khắc phục sự cố trong vòng 24h<br>
                    - Hàng hóa/dịch vụ hợp lệ, có nguồn gốc chính hãng
                </div>";
            } elseif ($item->product && $item->product->type == 'hosting') {
                $productDetails = "
                <div style='margin-top: 5px; color: #666; font-size: 9px; line-height: 1.5;'>
                    - Gói: {$productName}<br>
                    - Tên miền: {$domain}<br>
                    - Thời hạn: {$period} năm<br>
                    - Disk space: 10GB SSD<br>
                    - Bandwidth: Unlimited<br>
                    - Email accounts: 50<br>
                    - Control Panel: cPanel<br>
                    - Backup hàng ngày: Có
                </div>";
            } elseif ($item->product && $item->product->type == 'domain') {
                $productDetails = "
                <div style='margin-top: 5px; color: #666; font-size: 9px; line-height: 1.5;'>
                    - Tên miền: {$domain}<br>
                    - Thời hạn đăng ký: {$period} năm<br>
                    - Full DNS management<br>
                    - Domain theft protection<br>
                    - Email forwarding
                </div>";
            }

            $productsHtml .= "
            <tr>
                
                <td style='text-align: center; padding: 8px; border: 1px solid #ddd;'>{$item->quantity}</td>
                <td style='text-align: left; font-size: 9px; line-height: 1.5; padding: 8px; border: 1px solid #ddd; vertical-align: top;'>
                    <strong>Cung cấp {$productName} dành cho tên miền của website.</strong><br>
                    {$productDetails}
                </td>
                <td style='text-align: center; padding: 8px; border: 1px solid #ddd;'>{$item->quantity}</td>
                <td style='text-align: center; padding: 8px; border: 1px solid #ddd;'>{$period} năm</td>
                <td style='text-align: center; padding: 8px; border: 1px solid #ddd;'>Không giới hạn</td>
                <td style='text-align: center; padding: 8px; border: 1px solid #ddd;'>Không giới hạn</td>
                <td style='text-align: right; font-weight: bold; padding: 8px; border: 1px solid #ddd;'>" . number_format($item->subtotal, 0, ',', '.') . " đ</td>
                <td style='text-align: right; font-weight: bold; padding: 8px; border: 1px solid #ddd;'>" . number_format($item->subtotal, 0, ',', '.') . " đ</td>
            </tr>";
        }

        // Tạo phần QR code
        $qrCodeHtml = '';
        if (!empty($config->company_bank_qr_code)) {
            // Sử dụng đường dẫn tuyệt đối cho PDF
            $qrCodePath = storage_path('app/public/' . $config->company_bank_qr_code);
            
            if (file_exists($qrCodePath)) {
                // Chuyển ảnh thành base64 để embed vào PDF
                $imageData = base64_encode(file_get_contents($qrCodePath));
                $imageMimeType = mime_content_type($qrCodePath);
                
                $qrCodeHtml = "
                <img src='data:{$imageMimeType};base64,{$imageData}' 
                     alt='Payment QR Code' 
                     style='width: 150px; height: 150px; border: 2px solid #e9ecef; border-radius: 4px; margin: 0 auto 10px; display: block; object-fit: cover;'>
                ";
            } else {
                // Hiển thị thông tin thanh toán nếu không có QR
                $qrCodeHtml = "
                <div style='width: 150px; height: 150px; background: white; border: 2px solid #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 10px; color: #6c757d; text-align: center; line-height: 1.3; flex-direction: column;'>
                    <div style='font-weight: bold; margin-bottom: 8px;'>QR Code</div>
                    <div>Ngân hàng: " . ($config->bank_name ?? 'ACB') . "</div>
                    <div>TK: " . ($config->company_bank_account_number ?? '218906666') . "</div>
                    <div style='margin-top: 5px; color: #dc3545; font-weight: bold;'>" . number_format($total, 0, ',', '.') . " VNĐ</div>
                    <div style='margin-top: 5px; font-size: 9px;'>Ref: " . str_replace('QUOTE-', 'PAY-', $quoteNumber) . "</div>
                </div>";
            }
        }

        // Chuyển đổi số thành chữ
        $totalInWords = $this->convertNumberToWords($total);

        return "
<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Báo Giá {$quoteNumber}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            background: white;
            color: #333;
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            position: relative;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo {
            width: 60px;
            height: 40px;
            background: linear-gradient(45deg, #ff6b35, #4dabf7, #69db7c);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            border-radius: 4px;
        }
        .company-info {
            font-size: 14px;
            font-weight: bold;
            color: #4dabf7;
        }
        .stamp {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 120px;
            border: 3px solid #e74c3c;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #e74c3c;
            font-weight: bold;
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
        }
        .quote-title {
            position: absolute;
            top: 0;
            right: 0;
            text-align: right;
        }
        .quote-title h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .quote-date {
            font-size: 12px;
            color: #666;
        }
        .company-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 40px 0 20px 0;
        }
        .company-box {
            border: 1px solid #ddd;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .company-box h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            background: #e9ecef;
            padding: 5px;
            text-align: center;
            border-radius: 2px;
        }
        .company-details-content {
            font-size: 10px;
            line-height: 1.6;
        }
        .quotation-content {
            margin-top: 20px;
        }
        .section-title {
            background: #6c757d;
            color: white;
            padding: 8px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 10px;
            text-align: center;
            border-radius: 4px;
        }
        .quotation-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 20px;
        }
        .quotation-table th,
        .quotation-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            vertical-align: top;
        }
        .quotation-table th {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 9px;
        }
        .quotation-table td:first-child {
            text-align: left;
        }
        .item-details {
            text-align: left;
            font-size: 9px;
            line-height: 1.5;
        }
        .price-column {
            text-align: right;
            font-weight: bold;
        }
        .total-section {
            background: #f8f9fa;
            border: 1px solid #ddd;
        }
        .total-row {
            background: #e9ecef;
        }
        .payment-info {
            display: flex;
            gap: 20px;
            align-items: flex-start;
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
            border-radius: 4px;
        }
        .payment-details {
            flex: 1;
        }
        .payment-details table {
            margin: 0;
            width: 100%;
        }
        .payment-details td {
            border: none;
            padding: 8px 0;
        }
        .payment-details .amount {
            font-size: 16px;
            color: #dc3545;
            font-weight: bold;
        }
        .payment-details .reference {
            font-weight: bold;
            color: #28a745;
        }
        .qr-section {
            flex: 0 0 200px;
            text-align: center;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 2px dashed #dee2e6;
        }
        .qr-instructions {
            font-size: 10px;
            color: #666;
            margin-top: 10px;
            line-height: 1.4;
        }
        .payment-highlight {
            background: #e3f2fd;
            padding: 12px;
            border-radius: 4px;
            margin: 15px 0;
            border-left: 4px solid #2196f3;
            font-size: 11px;
        }
        .tech-specs {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
            font-size: 11px;
            line-height: 1.6;
            border-radius: 4px;
        }
        .footer-note {
            font-size: 9px;
            color: #666;
            margin-top: 10px;
            text-align: center;
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='logo-section'>
                <div class='logo'>LOGO</div>
                <div>
                    <div class='company-info'>" . ($config->company_name ?? 'CÔNG TY TNHH TMDV XD VÀ VC NGUYỄN TUẤN') . "</div>
                    <div style='font-size: 10px; color: #666;'>Technology Solutions</div>
                </div>
            </div>
            <div class='quote-title'>
                <h1>BÁO GIÁ</h1>
                <div class='quote-date'>
                    NGÀY TẠO: {$quoteDate}<br>
                    HIỆU LỰC: 30 ngày
                </div>
            </div>
        </div>

        <div class='company-details'>
            <div class='company-box'>
                <h3>BÊN CUNG CẤP DỊCH VỤ</h3>
                <div class='company-details-content'>
                    <strong>" . ($config->company_name ?? 'CÔNG TY TNHH TMDV XD VÀ VC NGUYỄN TUẤN') . "</strong><br>
                    Địa chỉ: " . ($config->company_address ?? 'Số 140 Nguyễn Văn Khối, Phường 8, Quận Gò Vấp, TP HCM') . "<br>
                    Điện thoại: " . ($config->support_phone ?? '0919 985 473') . "<br>
                    Email: " . ($config->support_email ?? 'supposthostit@gmail.com') . "<br>
                </div>
            </div>

            <div class='company-box'>
                <h3>KHÁCH HÀNG</h3>
                <div class='company-details-content'>
                    <strong>" . ($user->name ?? 'NISSAN HẢI PHÒNG') . "</strong><br><br>
                    Địa chỉ: " . ($user->address ?? '189 đường Hùng Vương (đường Hà Nội) Sở Dầu, Hồng Bàng, Hải Phòng') . "<br>
                    Điện thoại: " . ($user->phone ?? '024.3795.1555') . "<br>
                    Fax: <br>
                    Email: " . ($user->email ?? '') . "<br>
                    Website: " . ($user->website ?? 'www.nissanhaiphong.net') . "
                </div>
            </div>
        </div>

        <div class='quotation-content'>
            <div class='section-title'>
                NỘI DUNG: BÁO GIÁ DỊCH VỤ HOSTING VÀ CHỨNG THƯ SỐ
            </div>

            <table class='quotation-table'>
                <thead>
                    <tr>
                        <th style='width: 5%;'>#</th>
                        <th style='width: 35%;'>NỘI DUNG</th>
                        <th style='width: 8%;'>SỐ LƯỢNG</th>
                        <th style='width: 8%;'>THỜI HẠN<br>(NĂM)</th>
                        <th style='width: 8%;'>SERVER</th>
                        <th style='width: 8%;'>CẶP KHOÁ</th>
                        <th style='width: 10%;'>ĐƠN GIÁ<br>(VNĐ)</th>
                        <th style='width: 10%;'>THÀNH TIỀN<br>(VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    {$productsHtml}
                    <tr class='total-section'>
                        <td colspan='7' style='text-align: right; font-weight: bold;'>Tổng cộng</td>
                        <td class='price-column'>" . number_format($subtotal, 0, ',', '.') . " đ</td>
                    </tr>
                    <tr class='total-section'>
                        <td colspan='7' style='text-align: right;'>Giảm giá (10%)</td>
                        <td class='price-column'>" . number_format($discount, 0, ',', '.') . " đ</td>
                    </tr>
                    <tr class='total-section'>
                        <td colspan='7' style='text-align: right; font-weight: bold;'>Tổng sau giảm giá</td>
                        <td class='price-column'>" . number_format($afterDiscount, 0, ',', '.') . " đ</td>
                    </tr>
                    <tr class='total-section'>
                        <td colspan='7' style='text-align: right;'>Thuế VAT 10%</td>
                        <td class='price-column'>" . number_format($vat, 0, ',', '.') . " đ</td>
                    </tr>
                    <tr class='total-row'>
                        <td colspan='7' style='text-align: right; font-weight: bold; font-size: 11px;'>TỔNG THANH TOÁN</td>
                        <td class='price-column' style='font-weight: bold; font-size: 11px;'>" . number_format($total, 0, ',', '.') . " đ</td>
                    </tr>
                </tbody>
            </table>

            <div class='footer-note'>
                <strong>Bằng chữ: {$totalInWords} </strong><br>
                (Báo giá đã bao gồm thuế giá trị gia tăng và các khoản thuế, phí khác liên quan)
            </div>

            <div class='section-title'>THÔNG TIN THANH TOÁN</div>

            <div class='payment-info'>
                <div class='payment-details'>
                    <table>
                        <tr>
                            <td style='width: 35%; font-weight: bold; color: #495057;'>Số tiền:</td>
                            <td class='amount'>" . number_format($total, 0, ',', '.') . " VNĐ</td>
                        </tr>
                        <tr>
                            <td style='font-weight: bold; color: #495057;'>Ngân hàng:</td>
                            <td>" . ($config->bank_name ?? 'Ngân hàng Tiền Phong') . "</td>
                        </tr>
                        <tr>
                            <td style='font-weight: bold; color: #495057;'>Số tài khoản:</td>
                            <td style='font-weight: bold; color: #007bff;'>" . ($config->company_bank_account_number ?? '218906666') . "</td>
                        </tr>
                        <tr>
                            <td style='font-weight: bold; color: #495057;'>Chủ tài khoản:</td>
                            <td>" . ($config->company_name ?? 'NGUYEN VAN THIEN') . "</td>
                        </tr>
                        <tr>
                            <td style='font-weight: bold; color: #495057;'>Nội dung chuyển khoản:</td>
                            <td class='reference'>" . str_replace('QUOTE-', 'PAY-', $quoteNumber) . "</td>
                        </tr>
                        <tr>
                            <td style='font-weight: bold; color: #495057;'>Hạn thanh toán:</td>
                            <td style='color: #dc3545; font-weight: bold;'>{$expireDate}</td>
                        </tr>
                    </table>

                    <div class='payment-highlight'>
                        <strong>💡 Thanh toán nhanh:</strong> Quét mã QR để thanh toán ngay qua ứng dụng ngân hàng hoặc sử dụng thông tin tài khoản bên trên.
                    </div>
                </div>

                <div class='qr-section'>
                    {$qrCodeHtml}
                    
                    <div class='qr-instructions'>
                        <strong>📱 Cách thanh toán:</strong><br>
                        1. Mở ứng dụng ngân hàng<br>
                        2. Quét mã QR này<br>
                        3. Kiểm tra thông tin<br>
                        4. Xác nhận thanh toán
                    </div>
                </div>
            </div>
        </div>

        <div class='footer'>
            <p style='margin: 5px 0;'><strong>Cảm ơn quý khách đã tin tưởng dịch vụ của chúng tôi!</strong></p>
            <p style='margin: 5px 0;'>Mọi thắc mắc xin liên hệ: " . ($config->support_email ?? 'supposthostit@gmail.com') . " | " . ($config->support_phone ?? '0919 985 473') . "</p>
            <p style='margin: 5px 0;'>Báo giá này có hiệu lực đến ngày {$expireDate}</p>
        </div>
    </div>
</body>
</html>";
    }

    /**
     * Chuyển đổi số thành chữ (tiếng Việt)
     */
    private function convertNumberToWords($number)
    {
        $ones = array(
            '', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín',
            'mười', 'mười một', 'mười hai', 'mười ba', 'mười bốn', 'mười lăm',
            'mười sáu', 'mười bảy', 'mười tám', 'mười chín'
        );
        
        $tens = array('', '', 'hai mười', 'ba mười', 'bốn mười', 'năm mười', 'sáu mười', 'bảy mười', 'tám mười', 'chín mười');
        
        if ($number < 20) {
            return $ones[$number];
        } elseif ($number < 100) {
            return $tens[intval($number / 10)] . ' ' . $ones[$number % 10];
        } elseif ($number < 1000) {
            return $ones[intval($number / 100)] . ' trăm ' . $this->convertNumberToWords($number % 100);
        } elseif ($number < 1000000) {
            return $this->convertNumberToWords(intval($number / 1000)) . ' nghìn ' . $this->convertNumberToWords($number % 1000);
        } elseif ($number < 1000000000) {
            return $this->convertNumberToWords(intval($number / 1000000)) . ' triệu ' . $this->convertNumberToWords($number % 1000000);
        }
        
        return 'Số quá lớn';
    }

    /**
     * Tạo template email với thiết kế mới
     */
    private function createBeautifulEmailTemplate($data, $userMessage = '')
    {
        extract($data);

        // Tạo phần lời nhắn nếu có
        $messageSection = '';
        if (!empty($userMessage)) {
            $messageSection = "
            <table width='100%' border='0' cellpadding='15' cellspacing='0' style='background-color: #f8f9fa; margin: 20px 0;'>
                <tr>
                    <td style='border-left: 4px solid #007bff;'>
                        <p style='margin: 0; font-weight: bold; color: #333;'>Lời nhắn từ khách hàng:</p>
                        <p style='margin: 5px 0 0 0; color: #666;'>" . htmlspecialchars($userMessage) . "</p>
                    </td>
                </tr>
            </table>";
        }

        // Tạo danh sách sản phẩm cho email
        $itemsHtml = '';
        foreach ($cart->items as $index => $item) {
            $options = json_decode($item->options, true) ?: [];
            $period = $options['period'] ?? 1;
            $domain = $options['domain'] ?? null;
            $server = isset($options['server']) ? $options['server'] : 'Không giới hạn';

            $itemsHtml .= "
            <tr>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #ff0000; line-height: 18px; vertical-align: top; padding:10px 0;' class='article'>
                    Cung cấp " . ($item->product->name ?? 'SSL') . " cho website domain.<br/> -
                    Package: 01 " . ($item->product->name ?? 'SSL Certificate') . "<br/> - Domain in use:
                    " . ($domain ? '*.' . $domain : 'N/A') . "<br/> - Verification level: Domain verification<br/><br/>
                </td>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 18px; vertical-align: top; padding:10px 0;'>
                    <small>{$server}</small>
                </td>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 18px; vertical-align: top; padding:10px 0;' align='center'>
                    {$item->quantity}
                </td>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #1e2b33; line-height: 18px; vertical-align: top; padding:10px 0;' align='right'>
                    " . number_format($item->subtotal, 0, ',', '.') . " đ/năm
                </td>
            </tr>
            <tr>
                <td height='1' colspan='4' style='border-bottom:1px solid #e4e4e4'></td>
            </tr>";
        }

        // Technical specifications cho email
        $techSpecs = '';
        if (isset($cart->items[0]->product)) {
            $productType = $cart->items[0]->product->type;
            $productName = $cart->items[0]->product->name ?? '';

            if ($productType == 'ssl') {
                $isWildcard = strpos(strtolower($productName), 'wildcard') !== false;
                $isAlpha = strpos(strtolower($productName), 'alpha') !== false;

                $techSpecs = "
                <li>Certificate Type: {$productName}</li>
                <li>Website domain verification</li>
                <li>Key length from 2048 bit</li>
                <li>Security standard from 128 bit to 256 bit - RSA & DSA Algorithm Support</li>";

                if ($isWildcard) {
                    $techSpecs .= "<li>Wildcard extension support</li>";
                }

                $techSpecs .= "
                <li>Secure Site Seal: " . ($isAlpha ? 'Alpha Seal' : 'Secure Seal') . "</li>
                <li>Unlimited reissues and number of digital certificates issued</li>";

                if ($isWildcard) {
                    $techSpecs .= "<li>Unlimited first-level subdomains using digital certificate (*.*)</li>";
                }

                $techSpecs .= "
                <li>Compatible with 99.999% of browsers and operating systems</li>
                <li>Certificate warranty coverage of \$10,000 USD</li>";

            } elseif ($productType == 'hosting') {
                $techSpecs = "
                <li>Operating System: Linux</li>
                <li>Control Panel: cPanel</li>
                <li>PHP 5.6 - 8.2</li>
                <li>MySQL 5.7+</li>
                <li>Free Let's Encrypt SSL</li>
                <li>Daily Backup</li>
                <li>Anti-DDoS Protection</li>
                <li>99.9% Uptime Guarantee</li>
                <li>24/7 Technical Support</li>";

            } elseif ($productType == 'domain') {
                $techSpecs = "
                <li>Full DNS management</li>
                <li>Domain theft protection</li>
                <li>Email forwarding</li>
                <li>URL forwarding</li>
                <li>Custom nameservers</li>
                <li>Domain lock against unauthorized transfers</li>
                <li>Auto-renewal (optional)</li>";

            } else {
                $techSpecs = "
                <li>24/7 technical support</li>
                <li>Warranty according to manufacturer standards</li>
                <li>Latest version updates</li>
                <li>User documentation</li>";
            }
        }

        // QR Code section cho email (đơn giản hóa)
        $qrCodeSection = "
        <div style='width: 80px; height: 80px; border: 1px solid #ddd; padding: 3px; background-color: white; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #6c757d; text-align: center; line-height: 1.3; flex-direction: column;'>
            <div style='font-weight: bold; margin-bottom: 8px;'>QR Code</div>
            <div>Bank: " . ($config->bank_name ?? 'ACB') . "</div>
            <div>Account: " . ($config->company_bank_account_number ?? '218906666') . "</div>
            <div style='margin-top: 5px; color: #dc3545; font-weight: bold;'>" . number_format($total, 0, ',', '.') . " VNĐ</div>
            <div style='margin-top: 5px; font-size: 9px;'>Ref: " . str_replace('QUOTE-', 'PAY-', $quoteNumber) . "</div>
        </div>";

        return "
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <title>Quote Confirmation #{$quoteNumber}</title>
    <meta name='robots' content='noindex,nofollow' />
    <meta name='viewport' content='width=device-width; initial-scale=1.0;' />
    <style type='text/css'>
        @import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700);
        body { margin: 0; padding: 0; background: #e1e1e1; }
        div, p, a, li, td { -webkit-text-size-adjust: none; }
        .ReadMsgBody { width: 100%; background-color: #ffffff; }
        .ExternalClass { width: 100%; background-color: #ffffff; }
        body { width: 100%; height: 100%; background-color: #e1e1e1; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        html { width: 100%; }
        p { padding: 0 !important; margin-top: 0 !important; margin-right: 0 !important; margin-bottom: 0 !important; margin-left: 0 !important; }
        .visibleMobile { display: none; }
        .hiddenMobile { display: block; }
        .bg-gray { background-color: #f5f5f5; }
        .bold { font-weight: bold; }
        @media only screen and (max-width: 600px) {
            body { width: auto !important; }
            table[class=fullTable] { width: 96% !important; clear: both; }
            table[class=fullPadding] { width: 85% !important; clear: both; }
            table[class=col] { width: 45% !important; }
            .erase { display: none; }
        }
        @media only screen and (max-width: 420px) {
            table[class=fullTable] { width: 100% !important; clear: both; }
            table[class=fullPadding] { width: 85% !important; clear: both; }
            table[class=col] { width: 100% !important; clear: both; }
            table[class=col] td { text-align: left !important; }
            .erase { display: none; font-size: 0; max-height: 0; line-height: 0; padding: 0; }
            .visibleMobile { display: block !important; }
            .hiddenMobile { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#e1e1e1'>
        <tr><td height='20'></td></tr>
        <tr>
            <td>
                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#ffffff' style='border-radius: 10px 10px 0 0;'>
                    <tr class='hiddenMobile'><td height='40'></td></tr>
                    <tr class='visibleMobile'><td height='30'></td></tr>
                    <tr>
                        <td>
                            <table width='480' border='0' cellpadding='0' cellspacing='0' align='center' class='fullPadding'>
                                <tbody>
                                    <tr>
                                        <td>
                                            <table width='220' border='0' cellpadding='0' cellspacing='0' align='left' class='col'>
                                                <tbody>
                                                    <tr>
                                                        <td align='left'>
                                                            <div style='width: 32px; height: 32px; background: linear-gradient(45deg, #ff6b35, #4dabf7, #69db7c); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 8px;'>LOGO</div>
                                                        </td>
                                                    </tr>
                                                    <tr class='hiddenMobile'><td height='40'></td></tr>
                                                    <tr class='visibleMobile'><td height='20'></td></tr>
                                                    <tr>
                                                        <td style='font-size: 12px; color: #5b5b5b; font-family: \"Open Sans\", sans-serif; line-height: 18px; vertical-align: top; text-align: left;'>
                                                            Xin chào, " . ($user->name ?? 'Khách hàng') . ".<br>
                                                            Cảm ơn bạn đã mua hàng từ cửa hàng của chúng tôi.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <table width='220' border='0' cellpadding='0' cellspacing='0' align='right' class='col'>
                                                <tbody>
                                                    <tr class='visibleMobile'><td height='20'></td></tr>
                                                    <tr><td height='5'></td></tr>
                                                    <tr>
                                                        <td style='font-size: 21px; color: #ff0000; letter-spacing: -1px; font-family: \"Open Sans\", sans-serif; line-height: 1; vertical-align: top; text-align: right;'>
                                                            Báo Giá
                                                        </td>
                                                    </tr>
                                                    <tr class='hiddenMobile'><td height='50'></td></tr>
                                                    <tr class='visibleMobile'><td height='20'></td></tr>
                                                    <tr>
                                                        <td style='font-size: 12px; color: #5b5b5b; font-family: \"Open Sans\", sans-serif; line-height: 18px; vertical-align: top; text-align: right;'>
                                                            <small>SỐ</small> #{$quoteNumber}<br />
                                                            <small>NGÀY TẠO: {$quoteDate}<br />
                                                            HIỆU LỰC: {$validity}</small>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- /Header -->

    {$messageSection}

    <!-- Order Details -->
    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#e1e1e1'>
        <tbody>
            <tr>
                <td>
                    <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#ffffff'>
                        <tbody>
                            <tr class='hiddenMobile'><td height='60'></td></tr>
                            <tr class='visibleMobile'><td height='40'></td></tr>
                            <tr>
                                <td>
                                    <table width='480' border='0' cellpadding='0' cellspacing='0' align='center' class='fullPadding'>
                                        <tbody>
                                            <tr>
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 10px 7px 0;' width='52%' align='left'>SẢN PHẨM/MÔ TẢ</th>
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;' align='left'><small>SERVER</small></th>
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;' align='center'>Số lượng</th>
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #1e2b33; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;' align='right'>Thành tiền</th>
                                            </tr>
                                            <tr><td height='1' style='background: #bebebe;' colspan='4'></td></tr>
                                            <tr><td height='10' colspan='4'></td></tr>
                                            {$itemsHtml}
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr><td height='20'></td></tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Order Details -->

    <!-- Total -->
    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#e1e1e1'>
        <tbody>
            <tr>
                <td>
                    <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#ffffff'>
                        <tbody>
                            <tr>
                                <td>
                                    <table width='480' border='0' cellpadding='0' cellspacing='0' align='center' class='fullPadding'>
                                        <tbody>
                                            <tr>
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right;'>Tạm tính</td>
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;' width='80'>" . number_format($subtotal, 0, ',', '.') . " đ</td>
                                            </tr>
                                            <tr>
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right;'>Giảm giá (10%)</td>
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;' width='80'>-" . number_format($discount, 0, ',', '.') . " đ</td>
                                            </tr>
                                            <tr>
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right;'>VAT (10%)</td>
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;' width='80'>" . number_format($vat, 0, ',', '.') . " đ</td>
                                            </tr>
                                            <tr>
                                                <td style='font-size: 14px; font-family: \"Open Sans\", sans-serif; color: #1e2b33; line-height: 22px; vertical-align: top; text-align:right; font-weight: bold;'><strong>TỔNG CỘNG</strong></td>
                                                <td style='font-size: 14px; font-family: \"Open Sans\", sans-serif; color: #dc3545; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap; font-weight: bold;' width='80'><strong>" . number_format($total, 0, ',', '.') . " đ</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Total -->

    <!-- Information -->
    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#e1e1e1'>
        <tbody>
            <tr>
                <td>
                    <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#ffffff'>
                        <tbody>
                            <tr class='hiddenMobile'><td height='60'></td></tr>
                            <tr class='visibleMobile'><td height='40'></td></tr>
                            <tr>
                                <td>
                                    <table width='480' border='0' cellpadding='0' cellspacing='0' align='center' class='fullPadding'>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table width='220' border='0' cellpadding='0' cellspacing='0' align='left' class='col'>
                                                        <tbody>
                                                            <tr><td class='bg-gray bold'>BÊN CUNG CẤP</td></tr>
                                                            <tr>
                                                                <td>
                                                                    " . ($config->company_name ?? 'Công ty chúng tôi') . "<br />
                                                                    " . ($config->company_address ?? 'Địa chỉ công ty') . "<br />
                                                                    Email: " . ($config->support_email ?? 'supposthostit@gmail.com') . "<br />
                                                                    Website: " . ($config->website ?? 'www.company.com') . "
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table width='220' border='0' cellpadding='0' cellspacing='0' align='right' class='col'>
                                                        <tbody>
                                                            <tr><td class='bg-gray bold'>KHÁCH HÀNG</td></tr>
                                                            <tr>
                                                                <td>
                                                                    " . ($user->name ?? 'Khách hàng') . "<br />
                                                                    Địa chỉ: " . ($user->address ?? 'Chưa cung cấp') . "<br />
                                                                    Điện thoại: " . ($user->phone ?? 'N/A') . "<br />
                                                                    Email: " . ($user->email ?? '') . "<br />
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table width='480' border='0' cellpadding='0' cellspacing='0' align='center' class='fullPadding'>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table width='220' border='0' cellpadding='0' cellspacing='0' align='left' class='col'>
                                                        <tbody>
                                                            <tr><td class='bg-gray bold'>Thông tin thanh toán</td></tr>
                                                            <tr>
                                                                <td>
                                                                    <p><b>Số tiền:</b> " . number_format($total, 0, ',', '.') . " đ</p>
                                                                    <p><b>Ngân hàng:</b> " . ($config->bank_name ?? 'ACB') . "</p>
                                                                    <p><b>Số tài khoản:</b> " . ($config->company_bank_account_number ?? '218906666') . "</p>
                                                                    <p><b>Chủ tài khoản:</b> " . ($config->company_name ?? 'Công ty chúng tôi') . "</p>
                                                                    <p><b>Nội dung:</b> " . str_replace('QUOTE-', 'PAY-', $quoteNumber) . "</p>
                                                                    <p><b>Hạn thanh toán:</b> {$expireDate}</p>
                                                                    <div align='center' style='margin-top: 5px;'>
                                                                        <p>QR Code:</p>
                                                                        {$qrCodeSection}
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table width='220' border='0' cellpadding='0' cellspacing='0' align='right' class='col'>
                                                        <tbody>
                                                            <tr><td class='bg-gray bold'>Thông số kỹ thuật:</td></tr>
                                                            <tr>
                                                                <td>
                                                                    <ul style='margin: 0; padding-left: 20px;'>
                                                                        {$techSpecs}
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr class='hiddenMobile'><td height='60'></td></tr>
                            <tr class='visibleMobile'><td height='30'></td></tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <!-- /Information -->

    <table width='100%' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#e1e1e1'>
        <tr>
            <td>
                <table width='600' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#ffffff' style='border-radius: 0 0 10px 10px;'>
                    <tr>
                        <td>
                            <table width='480' border='0' cellpadding='0' cellspacing='0' align='center' class='fullPadding'>
                                <tbody>
                                    <tr>
                                        <td style='font-size: 12px; color: #5b5b5b; font-family: \"Open Sans\", sans-serif; line-height: 18px; vertical-align: top; text-align: left;'>
                                            Chúc bạn một ngày tốt lành.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr class='spacer'><td height='50'></td></tr>
                </table>
            </td>
        </tr>
        <tr><td height='20'></td></tr>
    </table>
</body>
</html>";
    }

    /**
     * Lấy giỏ hàng hiện tại
     */
    private function getCart()
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())
                ->with('items.product')
                ->first();
        } else {
            $sessionId = session()->getId();
            $cart = Cart::where('session_id', $sessionId)
                ->with('items.product')
                ->first();
        }

        return $cart;
    }
}