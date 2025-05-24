@extends('layouts.web.default')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-check-circle"></i> Yêu cầu nạp tiền đã được gửi</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset('assets/images/success.png') }}" alt="Success" class="img-fluid" style="max-width: 150px;">
                        <h5 class="mt-3">Cảm ơn bạn đã gửi yêu cầu nạp tiền!</h5>
                        <p>Yêu cầu của bạn đã được ghi nhận. Vui lòng thực hiện thanh toán theo hướng dẫn đã được gửi qua email.</p>
                    </div>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Thông tin giao dịch</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Mã giao dịch:</strong> {{ $depositData['transaction_code'] }}</p>
                                    <p class="mb-1"><strong>Ngày yêu cầu:</strong> {{ $depositData['date'] }}</p>
                                    <p class="mb-1"><strong>Số tiền:</strong> {{ number_format($depositData['amount'], 0, ',', '.') }} đ</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Phương thức:</strong>
                                        @if($depositData['payment_method'] == 'bank')
                                            Chuyển khoản ngân hàng
                                        @elseif($depositData['payment_method'] == 'momo')
                                            Ví MoMo
                                        @elseif($depositData['payment_method'] == 'zalopay')
                                            ZaloPay
                                        @endif
                                    </p>
                                    <p class="mb-1"><strong>Nội dung chuyển khoản:</strong> {{ $depositData['note_format'] }}</p>
                                    <p class="mb-0"><strong>Trạng thái:</strong> <span class="badge bg-warning">Chờ thanh toán</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Các bước tiếp theo</h5>
                        <ol class="mb-0">
                            <li>Vui lòng thực hiện thanh toán theo hướng dẫn đã được gửi đến email của bạn.</li>
                            <li>Nhớ sử dụng đúng nội dung chuyển khoản <strong>{{ $depositData['note_format'] }}</strong> để chúng tôi có thể xác nhận giao dịch của bạn.</li>
                            <li>Số dư tài khoản của bạn sẽ được cập nhật sau khi chúng tôi xác nhận giao dịch (thường trong vòng 24 giờ làm việc).</li>
                            <li>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi qua email: support@example.com</li>
                        </ol>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('customer.profile') }}" class="btn btn-primary">Về trang tài khoản</a>
                        <a href="{{ route('deposit') }}" class="btn btn-outline-primary">Tạo yêu cầu nạp tiền khác</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
