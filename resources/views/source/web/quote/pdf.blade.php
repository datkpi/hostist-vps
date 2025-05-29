<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Giá Dịch Vụ</title>
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

        .tech-specs {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
            font-size: 11px;
            line-height: 1.6;
            border-radius: 4px;
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

        .qr-code {
            width: 150px;
            height: 150px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 10px;
            color: #6c757d;
            text-align: center;
            line-height: 1.3;
            flex-direction: column;
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

        @media print {
            .container {
                max-width: none;
                padding: 10px;
            }
            
            body {
                font-size: 10px;
            }
        }

        @media (max-width: 768px) {
            .payment-info {
                flex-direction: column !important;
            }
            
            .qr-section {
                flex: none !important;
                margin-top: 20px;
            }
            
            .company-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-section">
                <div class="logo">LOGO</div>
                <div>
                    <div class="company-info">CÔNG TY CỦA BỌN TÔI</div>
                    <div style="font-size: 10px; color: #666;">Technology Solutions</div>
                </div>
            </div>
            
            <div class="stamp">
                <div>MST: 0123456789</div>
                <div style="margin: 5px 0;">★★★</div>
                <div>CÔNG TY TNHH</div>
                <div>CÔNG NGHỆ VÀ DỊCH VỤ</div>
                <div>VIỆT NAM</div>
            </div>

            <div class="quote-title">
                <h1>BÁO GIÁ</h1>
                <div class="quote-date">
                    NGÀY TẠO: 29/05/2025<br>
                    HIỆU LỰC: 30 ngày
                </div>
            </div>
        </div>

        <div class="company-details">
            <div class="company-box">
                <h3>BÊN CUNG CẤP DỊCH VỤ</h3>
                <div class="company-details-content">
                    <strong>CÔNG TY TNHH TMDV XD VÀ VC NGUYỄN TUẤN</strong><br>
                    Đại diện: Nguyễn Văn A (Mr.)<br>
                    Địa chỉ: Số 140 Nguyễn Văn Khối, Phường 8, Quận Gò Vấp, TP HCM<br>
                    Điện thoại: 0123456789<br>
                    Fax: 028.3xxx.xxxx<br>
                    Email: support@company.com<br>
                    Website: www.company.com
                </div>
            </div>

            <div class="company-box">
                <h3>KHÁCH HÀNG</h3>
                <div class="company-details-content">
                    <strong>CÔNG TY ABC</strong><br><br>
                    Địa chỉ: 123 Đường ABC, Quận XYZ, TP HCM<br>
                    Điện thoại: 0987654321<br>
                    Fax: 028.3xxx.xxxx<br>
                    Email: contact@abc.com<br>
                    Website: www.abc.com
                </div>
            </div>
        </div>

        <div class="quotation-content">
            <div class="section-title">
                NỘI DUNG: BÁO GIÁ DỊCH VỤ HOSTING VÀ CHỨNG THƯ SỐ SSL
            </div>

            <table class="quotation-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">SẢN PHẨM / DỊCH VỤ</th>
                        <th style="width: 15%; text-align: center;">SỐ LƯỢNG</th>
                        <th style="width: 25%; text-align: right;">THÀNH TIỀN (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="item-details">
                            <strong>Gói Hosting Business + SSL Certificate</strong><br>
                            <div style="margin-top: 5px; color: #666; font-size: 9px;">
                                • Gói: Business Hosting Package<br>
                                • Tên miền: example.com<br>
                                • Thời hạn: 1 năm<br>
                                • SSL Certificate: Let's Encrypt (miễn phí)<br>
                                • Disk space: 10GB SSD<br>
                                • Bandwidth: Unlimited<br>
                                • Email accounts: 50<br>
                                • Database: 10 MySQL<br>
                                • Control Panel: cPanel<br>
                                • Backup hàng ngày: Có<br>
                                • Hỗ trợ 24/7: Có
                            </div>
                        </td>
                        <td>1</td>
                        <td class="price-column">2,400,000</td>
                    </tr>
                    <tr>
                        <td class="item-details">
                            <strong>Dịch vụ thiết lập và cấu hình</strong><br>
                            <div style="margin-top: 5px; color: #666; font-size: 9px;">
                                • Cài đặt và cấu hình hosting<br>
                                • Thiết lập SSL certificate<br>
                                • Cấu hình email accounts<br>
                                • Hỗ trợ migration dữ liệu<br>
                                • Training sử dụng cPanel
                            </div>
                        </td>
                        <td>1</td>
                        <td class="price-column">500,000</td>
                    </tr>
                    <tr class="total-section">
                        <td colspan="2" style="text-align: right; font-weight: bold;">Tổng cộng</td>
                        <td class="price-column">2,900,000</td>
                    </tr>
                    <tr class="total-section">
                        <td colspan="2" style="text-align: right;">Giảm giá (10%)</td>
                        <td class="price-column">290,000</td>
                    </tr>
                    <tr class="total-section">
                        <td colspan="2" style="text-align: right; font-weight: bold;">Tổng sau giảm giá</td>
                        <td class="price-column">2,610,000</td>
                    </tr>
                    <tr class="total-section">
                        <td colspan="2" style="text-align: right;">Thuế VAT 10%</td>
                        <td class="price-column">261,000</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right; font-weight: bold; font-size: 11px;">TỔNG THANH TOÁN</td>
                        <td class="price-column" style="font-weight: bold; font-size: 11px;">2,871,000</td>
                    </tr>
                </tbody>
            </table>

            <div class="footer-note">
                <strong>Bằng chữ: Hai triệu tám trăm bảy mười một nghìn đồng./.strong><br>
                (Báo giá đã bao gồm thuế giá trị gia tăng và các khoản thuế, phí khác liên quan)
            </div>

            <div class="section-title">THÔNG TIN THANH TOÁN</div>

            <div class="payment-info">
                <div class="payment-details">
                    <table>
                        <tr>
                            <td style="width: 35%; font-weight: bold; color: #495057;">Số tiền:</td>
                            <td class="amount">2,871,000 VNĐ</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #495057;">Ngân hàng:</td>
                            <td>Ngân hàng ACB</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #495057;">Số tài khoản:</td>
                            <td style="font-weight: bold; color: #007bff;">218906666</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #495057;">Chủ tài khoản:</td>
                            <td>CÔNG TY TNHH TMDV XD VÀ VC NGUYỄN TUẤN</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #495057;">Nội dung chuyển khoản:</td>
                            <td class="reference">PAY-2025052901</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #495057;">Hạn thanh toán:</td>
                            <td style="color: #dc3545; font-weight: bold;">28/06/2025</td>
                        </tr>
                    </table>

                    <div class="payment-highlight">
                        <strong>💡 Thanh toán nhanh:</strong> Quét mã QR để thanh toán ngay qua ứng dụng ngân hàng hoặc sử dụng thông tin tài khoản bên trên.
                    </div>
                </div>

                <div class="qr-section">
                    <div class="qr-code">
                        <div style="font-weight: bold; margin-bottom: 8px;">QR Code</div>
                        <div>Ngân hàng: ACB</div>
                        <div>TK: 218906666</div>
                        <div style="margin-top: 5px; color: #dc3545; font-weight: bold;">2,871,000 VNĐ</div>
                        <div style="margin-top: 5px; font-size: 9px;">Ref: PAY-2025052901</div>
                    </div>
                    
                    <div class="qr-instructions">
                        <strong>📱 Cách thanh toán:</strong><br>
                        1. Mở ứng dụng ngân hàng<br>
                        2. Quét mã QR này<br>
                        3. Kiểm tra thông tin<br>
                        4. Xác nhận thanh toán
                    </div>
                </div>
            </div>

            <div class="section-title">THÔNG SỐ KỸ THUẬT</div>

            <div class="tech-specs">
                <strong>Hosting Business Package:</strong><br>
                • Hệ điều hành: Linux CentOS<br>
                • Control Panel: cPanel/WHM<br>
                • PHP: 5.6 - 8.2 (lựa chọn)<br>
                • MySQL: 5.7+ / MariaDB<br>
                • Disk Space: 10GB SSD<br>
                • Bandwidth: Unlimited<br>
                • Email Accounts: 50<br>
                • Database: 10 MySQL<br>
                • SSL Certificate: Let's Encrypt (miễn phí)<br>
                • Backup: Hàng ngày tự động<br>
                • Uptime: 99.9% guarantee<br>
                • Bảo mật: Anti-DDoS, Firewall<br>
                • Hỗ trợ: 24/7 qua ticket/email/phone
            </div>
        </div>

        <div class="footer">
            <p style="margin: 5px 0;"><strong>Cảm ơn quý khách đã tin tưởng dịch vụ của chúng tôi!</strong></p>
            <p style="margin: 5px 0;">Mọi thắc mắc xin liên hệ: support@company.com | 0123456789</p>
            <p style="margin: 5px 0;">Báo giá này có hiệu lực đến ngày 28/06/2025</p>
        </div>
    </div>
</body>
</html>