<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thông báo yêu cầu nạp tiền mới</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .container {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #0066cc;
            margin: 0;
        }
        .transaction-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .transaction-info p {
            margin: 5px 0;
        }
        .customer-info {
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .action-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Yêu cầu nạp tiền mới</h1>
            <p>Thông báo từ hệ thống</p>
        </div>

        <p>Hệ thống vừa nhận được một yêu cầu nạp tiền mới với thông tin sau:</p>

        <div class="transaction-info">
            <p><strong>Mã giao dịch:</strong> {{ $depositData['transaction_code'] }}</p>
            <p><strong>Ngày yêu cầu:</strong> {{ $depositData['date'] }}</p>
            <p><strong>Số tiền:</strong> {{ number_format($depositData['amount'], 0, ',', '.') }} đ</p>
            <p><strong>Phương thức thanh toán:</strong>
                @if($depositData['payment_method'] == 'bank')
                    Chuyển khoản ngân hàng
                @elseif($depositData['payment_method'] == 'momo')
                    Ví MoMo
                @elseif($depositData['payment_method'] == 'zalopay')
                    ZaloPay
                @endif
            </p>
            <p><strong>Nội dung chuyển khoản:</strong> {{ $depositData['note_format'] }}</p>
        </div>

        <div class="customer-info">
            <h3>Thông tin khách hàng</h3>
            <p><strong>ID khách hàng:</strong> {{ $depositData['customer_id'] }}</p>
            <p><strong>Tên khách hàng:</strong> {{ $depositData['customer_name'] }}</p>
            <p><strong>Email:</strong> {{ $depositData['customer_email'] }}</p>
        </div>

        <p>Khách hàng đã được thông báo chuyển khoản với nội dung <strong>{{ $depositData['note_format'] }}</strong>. Vui lòng kiểm tra và xác nhận giao dịch khi nhận được thanh toán.</p>

        <p>Truy cập vào hệ thống để xem chi tiết và xử lý yêu cầu:</p>

        <div style="text-align: center;">
            <a href="{{ url('/admin/deposits') }}" class="action-btn">Xem chi tiết trong hệ thống</a>
        </div>

        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống {{ config('app.name') }}.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
