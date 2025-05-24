<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo giá #{{ $quoteNumber }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 20px;
        }
        .quote-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .quote-info p {
            margin: 5px 0;
        }
        .quote-summary {
            margin-bottom: 20px;
        }
        .quote-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .quote-summary th,
        .quote-summary td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .quote-summary th {
            font-weight: bold;
        }
        .quote-total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .cta {
            text-align: center;
            margin: 20px 0;
        }
        .cta a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .custom-message {
            padding: 15px;
            background-color: #f5f5f5;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $companyName }}</h1>
            <p>Báo giá #{{ $quoteNumber }}</p>
        </div>

        <div class="content">
            <p>Kính gửi {{ $userName }},</p>

            <p>Cảm ơn bạn đã quan tâm đến dịch vụ của chúng tôi. Chúng tôi gửi đến bạn báo giá theo yêu cầu.</p>

            @if(!empty($message))
            <div class="custom-message">
                <p><strong>Lời nhắn:</strong></p>
                <p>{{ $message }}</p>
            </div>
            @endif

            <div class="quote-info">
                <p><strong>Ngày tạo báo giá:</strong> {{ $quoteDate }}</p>
                <p><strong>Ngày hết hạn:</strong> {{ $expireDate }}</p>
                <p><strong>Mã báo giá:</strong> {{ $quoteNumber }}</p>
            </div>

            <div class="quote-summary">
                <h3>Thông tin báo giá</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $item['period'] }} năm {{ $item['name'] }}</td>
                            <td>{{ number_format($item['subtotal'], 0, ',', '.') }} đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Tổng cộng</th>
                            <th>{{ number_format($total, 0, ',', '.') }} đ</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <p>Vui lòng kiểm tra file PDF đính kèm để xem chi tiết báo giá.</p>

            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email {{ $companyEmail }} hoặc số điện thoại {{ $companyPhone }}.</p>

            <p>Trân trọng,<br>
            {{ $companyName }}</p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ $companyName }}. Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
