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
        $expireDate = Carbon::now()->addDays(7)->format('d/m/Y');
        $subtotal = $cart->subtotal;
        $total = $subtotal;
        $validity = '7 days';

        // QR code path
        $qrCodePath = $config->company_bank_qr_code ?
            public_path('storage/' . $config->company_bank_qr_code) :
            public_path('images/qr-placeholder.png');

        try {
            // Tạo PDF với template đơn giản
            $pdf = $this->generateSimplePdf();

            // Chuẩn bị dữ liệu cho template email đẹp
            $data = compact(
                'cart',
                'user',
                'config',
                'quoteNumber',
                'quoteDate',
                'expireDate',
                'subtotal',
                'total',
                'qrCodePath',
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
     * Tạo template email
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

        // Tạo danh sách sản phẩm
        $itemsHtml = '';
        foreach ($cart->items as $index => $item) {
            $options = json_decode($item->options, true) ?: [];
            $period = $options['period'] ?? 1;
            $domain = $options['domain'] ?? null;
            $server = isset($options['server']) ? $options['server'] : 'Không giới hạn';

            $itemsHtml .= "
            <tr>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #ff0000; line-height: 18px; vertical-align: top; padding:10px 0;' class='article'>
                    Providing international public digital certificate " . ($item->product->name ?? 'SSL') . " for website domain.<br /> -
                    Package: 01 " . ($item->product->name ?? 'SSL Certificate') . "<br /> - Domain in use:
                    " . ($domain ? '*.' . $domain : 'N/A') . "<br /> - Verification level: Domain verification<br /><br />
                </td>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 18px; vertical-align: top; padding:10px 0;'>
                    <small>{$server}</small>
                </td>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 18px; vertical-align: top; padding:10px 0;' align='center'>
                    {$item->quantity}
                </td>
                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #1e2b33; line-height: 18px; vertical-align: top; padding:10px 0;' align='right'>
                    " . number_format($item->subtotal, 0, ',', '.') . "/đ/year
                </td>
            </tr>
            <tr>
                <td height='1' colspan='4' style='border-bottom:1px solid #e4e4e4'></td>
            </tr>";
        }

        // Tạo technical specifications dựa trên loại sản phẩm
        $techSpecs = '';
        if (isset($cart->items[0]->product) && $cart->items[0]->product->type == 'ssl') {
            $productName = $cart->items[0]->product->name ?? 'SSL Certificate';
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
                <li>Certificate warranty coverage of $10,000 USD</li>";
        } elseif (isset($cart->items[0]->product) && $cart->items[0]->product->type == 'hosting') {
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
        } elseif (isset($cart->items[0]->product) && $cart->items[0]->product->type == 'domain') {
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

        // QR Code section
        $qrCodeSection = '';
        if (isset($qrCodePath) && file_exists($qrCodePath)) {
            $qrCodeSection = "<img src='{$qrCodePath}' alt='QR Code' style='width: 80px; height: 80px; border: 1px solid #ddd; padding: 3px; background-color: white;' />";
        } else {
            $qrCodeSection = "<div style='width: 80px; height: 80px; border: 1px solid #ddd; padding: 3px; background-color: white; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 10px;'>QR Code</div>";
        }

        return "
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <title>Quote Confirmation</title>
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
                                                            <img src='http://www.supah.it/dribbble/017/logo.png' width='32' height='32' alt='logo' border='0' />
                                                        </td>
                                                    </tr>
                                                    <tr class='hiddenMobile'><td height='40'></td></tr>
                                                    <tr class='visibleMobile'><td height='20'></td></tr>
                                                    <tr>
                                                        <td style='font-size: 12px; color: #5b5b5b; font-family: \"Open Sans\", sans-serif; line-height: 18px; vertical-align: top; text-align: left;'>
                                                            Hello, " . ($user->name ?? 'Customer') . ".<br>
                                                            Thank you for shopping from our store and for your order.
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
                                                            Quote
                                                        </td>
                                                    </tr>
                                                    <tr class='hiddenMobile'><td height='50'></td></tr>
                                                    <tr class='visibleMobile'><td height='20'></td></tr>
                                                    <tr>
                                                        <td style='font-size: 12px; color: #5b5b5b; font-family: \"Open Sans\", sans-serif; line-height: 18px; vertical-align: top; text-align: right;'>
                                                            <small>ORDER</small> #{$quoteNumber}<br />
                                                            <small>CREATED DATE: {$quoteDate}<br />
                                                            VALID FOR: {$validity}</small>
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
                 <!-- Tiêu đề nội dung -->
    <table width='50%' border='0' cellpadding='0' cellspacing='0' align='center' class='fullTable' bgcolor='#e1e1e1'>
        <tr>
            <td align='center' class='bg-gray bold'>
                CONTENTS: QUOTATION FOR " . strtoupper($cart->items[0]->product->type ?? 'SSL') . " PACKAGE FOR WEBSITE
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
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 10px 7px 0;' width='52%' align='left'>ITEM/DESCRIPTION</th>
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;' align='left'><small>SERVER</small></th>
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;' align='center'>Quantity</th>
                                                <th style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #1e2b33; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;' align='right'>Subtotal</th>
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
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right;'>Subtotal</td>
                                                <td style='font-size: 12px; font-family: \"Open Sans\", sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;' width='80'>" . number_format($total, 0, ',', '.') . " đ</td>
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
                                                            <tr><td class='bg-gray bold'>PROVIDER</td></tr>
                                                            <tr>
                                                                <td>
                                                                    " . ($config->company_name ?? 'Hostist company') . "<br />
                                                                    " . ($config->company_address ?? '5335 Gate Pkwy, 2nd Floor, Jacksonville, FL 32256') . "<br />
                                                                    Email: " . ($config->support_email ?? 'supporthostit@gmail.com') . "<br />
                                                                    URL: " . ($config->website ?? 'www.hostist.com') . "
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table width='220' border='0' cellpadding='0' cellspacing='0' align='right' class='col'>
                                                        <tbody>
                                                            <tr><td class='bg-gray bold'>CLIENT</td></tr>
                                                            <tr>
                                                                <td>
                                                                    " . ($user->name ?? 'Customer') . "<br />
                                                                    Address: " . ($user->address ?? 'Address not provided') . "<br />
                                                                    Phone: " . ($user->phone ?? 'N/A') . "<br />
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
                                                        <tr><td class='bg-gray bold'>Payment Information</td></tr>
                                                            <tr>
                                                                <td>
                                                                    <p><b></b></p>
                                                                    <p><b>Amount:</b> " . number_format($total, 0, ',', '.') . " đ</p>
                                                                    <p><b>Bank:</b> " . ($config->bank_name ?? 'ACB') . "</p>
                                                                    <p><b>Account Number:</b> " . ($config->company_bank_account_number ?? '218906666') . "</p>
                                                                    <p><b>Account Holder:</b> " . ($config->company_name ?? 'Hostist company') . "</p>
                                                                    <p><b>Reference:</b> " . str_replace('QUOTE-', 'INV', $quoteNumber) . "</p>
                                                                    <p><b>Expiration Date:</b> {$expireDate}</p>
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
                                                        <tr><td class='bg-gray bold'>Standard Technical Specifications:</td></tr>
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
                                            Have a nice day.
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
     * Tạo PDF đơn giản để tránh lỗi cellmap
     */
    private function generateSimplePdf()
    {
        // Lấy giỏ hàng hiện tại
        $cart = $this->getCart();
        $user = Auth::user();
        $config = Config::current();

        // Tạo số báo giá
        $quoteNumber = 'QUOTE-' . date('Ymd') . '-' . str_pad($cart->id, 4, '0', STR_PAD_LEFT);
        $quoteDate = Carbon::now()->format('d/m/Y');
        $expireDate = Carbon::now()->addDays(7)->format('d/m/Y');
        $subtotal = $cart->subtotal;
        $total = $subtotal;

        // Tạo HTML đơn giản cho PDF
        $html = $this->createPdfTemplate($cart, $user, $config, $quoteNumber, $quoteDate, $expireDate, $total);

        $pdf = PDF::loadHTML($html);

        // Thiết lập options an toàn cho PDF
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false, // Tắt remote để tránh lỗi
            'defaultFont' => 'DejaVu Sans', // Font an toàn
            'dpi' => 150,
            'defaultMediaType' => 'print',
            'isFontSubsettingEnabled' => true,
        ]);

        return $pdf;
    }

    /**
     * Tạo PDF với template phức tạp (backup method)
     */
    private function generatePdf()
    {
        try {
            return $this->generateSimplePdf();
        } catch (\Exception $e) {
            // Fallback to simple PDF if complex one fails
            return $this->generateSimplePdf();
        }
    }

    /**
     * Tạo template HTML cho PDF - phiên bản đẹp nhưng đơn giản
     */
    private function createPdfTemplate($cart, $user, $config, $quoteNumber, $quoteDate, $expireDate, $total)
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
                    <div style='font-size: 10px; color: #666; margin-top: 5px;'>
                        • Certificate Type: {$productName}<br>
                        • Domain: " . ($domain !== 'N/A' ? "*.$domain" : 'N/A') . "<br>
                        • Verification: Domain Verification<br>
                        • Period: {$period} year(s)
                    </div>";
            } elseif ($item->product && $item->product->type == 'hosting') {
                $productDetails = "
                    <div style='font-size: 10px; color: #666; margin-top: 5px;'>
                        • Package: {$productName}<br>
                        • Domain: {$domain}<br>
                        • Period: {$period} year(s)
                    </div>";
            } elseif ($item->product && $item->product->type == 'domain') {
                $productDetails = "
                    <div style='font-size: 10px; color: #666; margin-top: 5px;'>
                        • Domain: {$domain}<br>
                        • Registration Period: {$period} year(s)
                    </div>";
            }

            $productsHtml .= "
            <tr>
                <td style='padding: 12px 8px; border-bottom: 1px solid #ddd; vertical-align: top;'>
                    <strong>{$productName}</strong>
                    {$productDetails}
                </td>
                <td style='padding: 12px 8px; border-bottom: 1px solid #ddd; text-align: center;'>{$item->quantity}</td>
                <td style='padding: 12px 8px; border-bottom: 1px solid #ddd; text-align: right;'>" . number_format($item->subtotal, 0, ',', '.') . " VNĐ</td>
            </tr>";
        }

        // Technical specifications
        $techSpecs = '';
        if (isset($cart->items[0]->product)) {
            $productType = $cart->items[0]->product->type;
            $productName = $cart->items[0]->product->name ?? '';

            if ($productType == 'ssl') {
                $isWildcard = strpos(strtolower($productName), 'wildcard') !== false;
                $isAlpha = strpos(strtolower($productName), 'alpha') !== false;

                $techSpecs = "
                    • Certificate Type: {$productName}<br>
                    • Website domain verification<br>
                    • Key length from 2048 bit<br>
                    • Security: 128-256 bit encryption<br>
                    " . ($isWildcard ? "• Wildcard support<br>" : "") . "
                    • Site Seal: " . ($isAlpha ? 'Alpha Seal' : 'Secure Seal') . "<br>
                    • Unlimited reissues<br>
                    " . ($isWildcard ? "• Unlimited subdomains<br>" : "") . "
                    • 99.999% browser compatibility<br>
                    • $10,000 USD warranty coverage";
            } elseif ($productType == 'hosting') {
                $techSpecs = "
                    • Operating System: Linux<br>
                    • Control Panel: cPanel<br>
                    • PHP 5.6 - 8.2<br>
                    • MySQL 5.7+<br>
                    • Free Let's Encrypt SSL<br>
                    • Daily Backup<br>
                    • Anti-DDoS Protection<br>
                    • 99.9% Uptime Guarantee<br>
                    • 24/7 Technical Support";
            } elseif ($productType == 'domain') {
                $techSpecs = "
                    • Full DNS management<br>
                    • Domain theft protection<br>
                    • Email forwarding<br>
                    • URL forwarding<br>
                    • Custom nameservers<br>
                    • Transfer lock protection<br>
                    • Auto-renewal available";
            } else {
                $techSpecs = "
                    • 24/7 technical support<br>
                    • Manufacturer warranty<br>
                    • Latest version updates<br>
                    • Complete documentation";
            }
        }

        return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Quote #{$quoteNumber}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .quote-title {
            font-size: 20px;
            color: #ff0000;
            margin-bottom: 15px;
        }
        .quote-info {
            font-size: 11px;
            color: #666;
        }
        .section-title {
            background-color: #f5f5f5;
            padding: 8px 12px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            border: 1px solid #ddd;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-left {
            width: 48%;
            float: left;
            padding-right: 2%;
        }
        .info-right {
            width: 48%;
            float: right;
            padding-left: 2%;
        }
        .clear {
            clear: both;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background-color: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
        }
        td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }
        .total-row {
            background-color: #f9f9f9;
            font-weight: bold;
            border-top: 2px solid #ddd;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
        }
        .tech-specs {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
            font-size: 11px;
            line-height: 1.6;
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
    <div class='header'>
        <div class='company-name'>" . ($config->company_name ?? 'HOSTIST COMPANY') . "</div>
        <div class='quote-title'>QUOTE #{$quoteNumber}</div>
        <div class='quote-info'>
            Created: {$quoteDate} | Valid until: {$expireDate}
        </div>
    </div>

    <div class='section-title'>COMPANY & CLIENT INFORMATION</div>

    <div class='info-grid'>
        <div class='info-left'>
            <h4 style='margin: 0 0 10px 0; color: #333;'>PROVIDER</h4>
            <strong>" . ($config->company_name ?? 'Hostist Company') . "</strong><br>
            " . ($config->company_address ?? '5335 Gate Pkwy, 2nd Floor, Jacksonville, FL 32256') . "<br>
            Email: " . ($config->support_email ?? 'support@hostist.com') . "<br>
            Website: " . ($config->website ?? 'www.hostist.com') . "<br>
            Phone: " . ($config->support_phone ?? 'N/A') . "
        </div>

        <div class='info-right'>
            <h4 style='margin: 0 0 10px 0; color: #333;'>CLIENT</h4>
            <strong>" . ($user->name ?? 'Customer') . "</strong><br>
            " . ($user->address ?? 'Address not provided') . "<br>
            Email: " . ($user->email ?? 'N/A') . "<br>
            Phone: " . ($user->phone ?? 'N/A') . "
        </div>
    </div>

    <div class='clear'></div>

    <div class='section-title'>QUOTATION DETAILS</div>

    <table>
        <thead>
            <tr>
                <th style='width: 60%;'>Product / Service</th>
                <th style='width: 15%; text-align: center;'>Qty</th>
                <th style='width: 25%; text-align: right;'>Amount</th>
            </tr>
        </thead>
        <tbody>
            {$productsHtml}
            <tr class='total-row'>
                <td colspan='2' style='text-align: right; font-size: 14px;'><strong>TOTAL AMOUNT:</strong></td>
                <td style='text-align: right; font-size: 14px;'><strong>" . number_format($total, 0, ',', '.') . " VNĐ</strong></td>
            </tr>
        </tbody>
    </table>

    <div class='section-title'>PAYMENT INFORMATION</div>

    <div class='payment-info'>
        <table style='margin: 0;'>
            <tr>
                <td style='border: none; padding: 5px 0; width: 30%;'><strong>Amount:</strong></td>
                <td style='border: none; padding: 5px 0;'>" . number_format($total, 0, ',', '.') . " VNĐ</td>
            </tr>
            <tr>
                <td style='border: none; padding: 5px 0;'><strong>Bank:</strong></td>
                <td style='border: none; padding: 5px 0;'>" . ($config->bank_name ?? 'ACB Bank') . "</td>
            </tr>
            <tr>
                <td style='border: none; padding: 5px 0;'><strong>Account Number:</strong></td>
                <td style='border: none; padding: 5px 0;'>" . ($config->company_bank_account_number ?? '218906666') . "</td>
            </tr>
            <tr>
                <td style='border: none; padding: 5px 0;'><strong>Account Holder:</strong></td>
                <td style='border: none; padding: 5px 0;'>" . ($config->company_name ?? 'Hostist Company') . "</td>
            </tr>
            <tr>
                <td style='border: none; padding: 5px 0;'><strong>Payment Reference:</strong></td>
                <td style='border: none; padding: 5px 0;'>" . str_replace('QUOTE-', 'PAY-', $quoteNumber) . "</td>
            </tr>
            <tr>
                <td style='border: none; padding: 5px 0;'><strong>Payment Due:</strong></td>
                <td style='border: none; padding: 5px 0;'>{$expireDate}</td>
            </tr>
        </table>
    </div>

    <div class='section-title'>TECHNICAL SPECIFICATIONS</div>

    <div class='tech-specs'>
        {$techSpecs}
    </div>

    <div class='footer'>
        <p style='margin: 5px 0;'><strong>Thank you for choosing " . ($config->company_name ?? 'Hostist Company') . "</strong></p>
        <p style='margin: 5px 0;'>For questions or support, please contact us at " . ($config->support_email ?? 'support@hostist.com') . "</p>
        <p style='margin: 5px 0;'>This quote is valid until {$expireDate}</p>
    </div>
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
