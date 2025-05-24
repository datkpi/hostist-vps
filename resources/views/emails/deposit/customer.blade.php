<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Yêu cầu nạp tiền</title>
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

        .payment-info {
            margin-bottom: 20px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .note {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Yêu cầu nạp tiền</h1>
            <p>Cảm ơn bạn đã gửi yêu cầu nạp tiền</p>
        </div>

        <p>Kính gửi {{ $depositData['customer_name'] }},</p>

        <p>Chúng tôi đã nhận được yêu cầu nạp tiền từ bạn. Dưới đây là thông tin chi tiết:</p>

        <div class="transaction-info">
            <p><strong>Mã giao dịch:</strong> {{ $depositData['transaction_code'] }}</p>
            <p><strong>Ngày yêu cầu:</strong> {{ $depositData['date'] }}</p>
            <p><strong>Số tiền:</strong> {{ number_format($depositData['amount'], 0, ',', '.') }} đ</p>
            <p><strong>Phương thức thanh toán:</strong>
                @if ($depositData['payment_method'] == 'bank')
                    Chuyển khoản ngân hàng
                @elseif($depositData['payment_method'] == 'momo')
                    Ví MoMo
                @elseif($depositData['payment_method'] == 'zalopay')
                    ZaloPay
                @endif
            </p>
        </div>

        <div class="payment-info">
            <h3>Thông tin thanh toán</h3>

            @if ($depositData['payment_method'] == 'bank')
                @if (isset($depositData['bank_info']))
                    <p><strong>Ngân hàng:</strong> {{ $depositData['bank_info']['bank_name'] ?? 'Đang cập nhật' }}</p>
                    <p><strong>Số tài khoản:</strong>
                        {{ $depositData['bank_info']['account_number'] ?? 'Đang cập nhật' }}</p>
                    <p><strong>Chủ tài khoản:</strong>
                        {{ $depositData['bank_info']['account_name'] ?? 'Đang cập nhật' }}</p>
                    <p><strong>Chi nhánh:</strong> {{ $depositData['bank_info']['branch'] ?? 'Đang cập nhật' }}</p>
                @else
                    <p>Thông tin ngân hàng đang được cập nhật. Vui lòng liên hệ với chúng tôi để biết chi tiết.</p>
                @endif
            @elseif($depositData['payment_method'] == 'momo')
                @if (isset($depositData['momo_info']))
                    <p><strong>Số điện thoại MoMo:</strong> {{ $depositData['momo_info']['phone'] ?? 'Đang cập nhật' }}
                    </p>
                    <p><strong>Tên tài khoản:</strong>
                        {{ $depositData['momo_info']['account_name'] ?? 'Đang cập nhật' }}</p>
                @else
                    <p>Thông tin MoMo đang được cập nhật. Vui lòng liên hệ với chúng tôi để biết chi tiết.</p>
                @endif
            @elseif($depositData['payment_method'] == 'zalopay')
                @if (isset($depositData['zalopay_info']))
                    <p><strong>Số điện thoại ZaloPay:</strong>
                        {{ $depositData['zalopay_info']['phone'] ?? 'Đang cập nhật' }}</p>
                    <p><strong>Tên tài khoản:</strong>
                        {{ $depositData['zalopay_info']['account_name'] ?? 'Đang cập nhật' }}</p>
                @else
                    <p>Thông tin ZaloPay đang được cập nhật. Vui lòng liên hệ với chúng tôi để biết chi tiết.</p>
                @endif
            @endif

            <p><strong>Nội dung chuyển khoản:</strong> {{ $depositData['note_format'] }}</p>

            @if (isset($depositData['qr_code_url']))
                <div class="qr-code">
                    <img src="{{ $depositData['qr_code_url'] }}" alt="QR Code" style="max-width: 200px;">
                    <p>Quét mã QR để thanh toán</p>
                </div>
            @endif
        </div>

        <div class="note">
            <p><strong>Lưu ý quan trọng:</strong></p>
            <ul>
                <li>Vui lòng sử dụng đúng nội dung chuyển khoản <strong>{{ $depositData['note_format'] }}</strong> khi
                    thực hiện thanh toán.</li>
                <li>Số dư tài khoản của bạn sẽ được cập nhật sau khi chúng tôi xác nhận giao dịch (thường trong vòng 24
                    giờ làm việc).</li>
                <li>Nếu bạn đã thanh toán nhưng chưa nhận được xác nhận sau 24 giờ, vui lòng liên hệ với chúng tôi.</li>
            </ul>
        </div>

        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua email hoặc hotline.</p>

        <p>Trân trọng,<br>Đội ngũ hỗ trợ {{ config('app.name') }}</p>

        <div class="footer">
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tất cả các quyền được bảo lưu.</p>
        </div>
    </div>
</body>

</html>
