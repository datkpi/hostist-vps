@extends('layouts.web.default')

@section('content')
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Nạp tiền</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('deposit.process') }}">
                            @csrf

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="text-primary mb-0">Thông tin tài khoản</h5>
                                    <span class="badge bg-success p-2">
                                        Số dư hiện tại:
                                        {{ $customer ? number_format($customer->balance ?? 0, 0, ',', '.') : 0 }} đ
                                    </span>
                                </div>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Khách hàng:</strong> {{ auth()->user()->name }}
                                                </p>
                                                <p class="mb-1"><strong>Email:</strong> {{ auth()->user()->email }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Mã khách hàng:</strong>
                                                    {{ $customer ? $customer->id : 'Chưa cập nhật' }}</p>
                                                <p class="mb-1"><strong>Số điện thoại:</strong>
                                                    {{ auth()->user()->phone ?? 'Chưa cung cấp' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="card border-primary">
                                    <div class="card-body bg-gradient"
                                        style="background: linear-gradient(135deg, #f5f7fa 0%, #e4eff8 100%);">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 text-center">
                                                <i class="fas fa-gift fa-3x text-primary"></i>
                                            </div>
                                            <div class="col-md-10">
                                                <h4 class="text-primary mb-2">🎁 Ưu đãi đặc biệt</h4>
                                                <p class="mb-1"><strong>Nạp từ 10.000.000đ trở lên - Nhận ngay thưởng
                                                        5%</strong></p>
                                                <p class="mb-0 text-success">
                                                    Ví dụ: Nạp 10.000.000đ → Nhận ngay <strong>10.500.000đ</strong> vào tài
                                                    khoản!
                                                </p>
                                                <div class="progress mt-2" style="height: 10px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                                        role="progressbar" style="width: 100%" aria-valuenow="100"
                                                        aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <p class="small mt-1 mb-0 text-muted">Khuyến mãi áp dụng cho mọi phương thức
                                                    thanh toán</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">Chọn số tiền nạp</h5>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="btn-group w-100" role="group">
                                            @foreach ($depositAmounts as $label => $amount)
                                                <input type="radio" class="btn-check" name="amount_preset"
                                                    id="amount_{{ $label }}" value="{{ $amount }}"
                                                    autocomplete="off">
                                                <label class="btn btn-outline-primary"
                                                    for="amount_{{ $label }}">{{ $label }} triệu</label>
                                            @endforeach
                                            <input type="radio" class="btn-check" name="amount_preset" id="amount_custom"
                                                value="custom" autocomplete="off" checked>
                                            <label class="btn btn-outline-primary" for="amount_custom">Khác</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3" id="custom_amount_container">
                                    <label for="amount" class="form-label">Số tiền nạp (VND) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="amount" name="amount"
                                        value="{{ old('amount', $minDeposit) }}" min="{{ $minDeposit }}"
                                        max="{{ $maxDeposit }}" required>
                                    <div class="form-text">Số tiền nạp tối thiểu:
                                        {{ number_format($minDeposit, 0, ',', '.') }} đ</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="text-primary mb-3">Chọn phương thức thanh toán</h5>

                                <div class="row">
                                    @if ($config->company_bank_account_number)
                                        <div class="col-md-4 mb-3">
                                            <div class="card payment-method-card">
                                                <div class="card-body text-center">
                                                    <input type="radio" class="btn-check" name="payment_method"
                                                        id="payment_bank" value="bank" autocomplete="off" checked>
                                                    <label
                                                        class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center"
                                                        for="payment_bank">
                                                        <i class="fas fa-university fa-3x mb-2"></i>
                                                        <span>Chuyển khoản ngân hàng</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($config->momo_phone_number)
                                        <div class="col-md-4 mb-3">
                                            <div class="card payment-method-card">
                                                <div class="card-body text-center">
                                                    <input type="radio" class="btn-check" name="payment_method"
                                                        id="payment_momo" value="momo" autocomplete="off">
                                                    <label
                                                        class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center"
                                                        for="payment_momo">
                                                        <i class="fas fa-wallet fa-3x mb-2"></i>
                                                        <span>Ví MoMo</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($config->zalopay_phone_number)
                                        <div class="col-md-4 mb-3">
                                            <div class="card payment-method-card">
                                                <div class="card-body text-center">
                                                    <input type="radio" class="btn-check" name="payment_method"
                                                        id="payment_zalopay" value="zalopay" autocomplete="off">
                                                    <label
                                                        class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center"
                                                        for="payment_zalopay">
                                                        <i class="fas fa-wallet fa-3x mb-2"></i>
                                                        <span>ZaloPay</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="payment-info mt-3">
                                    <!-- Thông tin chuyển khoản ngân hàng -->
                                    <div id="bank_info" class="payment-details">
                                        <div class="alert alert-info">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <p class="mb-1"><strong>Ngân hàng:</strong>
                                                        {{ $config->company_bank_name }}</p>
                                                    <p class="mb-1"><strong>Số tài khoản:</strong>
                                                        {{ $config->company_bank_account_number }}</p>
                                                    <p class="mb-1"><strong>Chủ tài khoản:</strong>
                                                        {{ $config->company_bank_account_name }}</p>
                                                    <p class="mb-1"><strong>Chi nhánh:</strong>
                                                        {{ $config->company_bank_branch }}</p>
                                                    <p class="mb-0"><strong>Nội dung chuyển khoản:</strong> <span
                                                            id="transfer_note">{{ str_replace('{customer_id}', $customer->id, $config->deposit_note_format ?? "NapTien{$customer->id}") }}</span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    @if ($config->company_bank_qr_code)
                                                        <img src="{{ asset('storage/' . $config->company_bank_qr_code) }}"
                                                            alt="QR Code" class="img-fluid" style="max-height: 120px;">
                                                        <p class="small mt-1">Quét mã QR để thanh toán</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Thông tin MoMo -->
                                    <div id="momo_info" class="payment-details" style="display: none;">
                                        <div class="alert alert-info">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <p class="mb-1"><strong>Số điện thoại MoMo:</strong>
                                                        {{ $config->momo_phone_number }}</p>
                                                    <p class="mb-1"><strong>Tên tài khoản:</strong>
                                                        {{ $config->momo_account_name }}</p>
                                                    <p class="mb-0"><strong>Nội dung chuyển khoản:</strong>
                                                        <span>{{ str_replace('{customer_id}', $customer->id, $config->deposit_note_format ?? "NapTien{$customer->id}") }}</span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    @if ($config->momo_qr_code)
                                                        <img src="{{ asset('storage/' . $config->momo_qr_code) }}"
                                                            alt="QR Code MoMo" class="img-fluid"
                                                            style="max-height: 120px;">
                                                        <p class="small mt-1">Quét mã QR để thanh toán</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Thông tin ZaloPay -->
                                    <div id="zalopay_info" class="payment-details" style="display: none;">
                                        <div class="alert alert-info">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <p class="mb-1"><strong>Số điện thoại ZaloPay:</strong>
                                                        {{ $config->zalopay_phone_number }}</p>
                                                    <p class="mb-1"><strong>Tên tài khoản:</strong>
                                                        {{ $config->zalopay_account_name }}</p>
                                                    <p class="mb-0"><strong>Nội dung chuyển khoản:</strong>
                                                        <span>{{ str_replace('{customer_id}', $customer->id, $config->deposit_note_format ?? "NapTien{$customer->id}") }}</span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    @if ($config->zalopay_qr_code)
                                                        <img src="{{ asset('storage/' . $config->zalopay_qr_code) }}"
                                                            alt="QR Code ZaloPay" class="img-fluid"
                                                            style="max-height: 120px;">
                                                        <p class="small mt-1">Quét mã QR để thanh toán</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($config->deposit_instruction)
                                <div class="mb-4">
                                    <h5 class="text-primary mb-3">Hướng dẫn nạp tiền</h5>
                                    <div class="alert alert-warning">
                                        {!! $config->deposit_instruction !!}
                                    </div>
                                </div>
                            @endif

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms"
                                        required>
                                    <label class="form-check-label" for="agree_terms">
                                        Tôi đã đọc và đồng ý với các điều khoản nạp tiền
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Tiến hành nạp tiền</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý hiển thị phương thức thanh toán
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const paymentDetails = document.querySelectorAll('.payment-details');

            function showPaymentDetails() {
                const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;

                paymentDetails.forEach(detail => {
                    detail.style.display = 'none';
                });

                document.getElementById(selectedMethod + '_info').style.display = 'block';
            }

            paymentMethods.forEach(method => {
                method.addEventListener('change', showPaymentDetails);
            });

            // Khởi tạo hiển thị
            showPaymentDetails();

            // Xử lý chọn số tiền
            const amountPresets = document.querySelectorAll('input[name="amount_preset"]');
            const amountInput = document.getElementById('amount');
            const customAmountContainer = document.getElementById('custom_amount_container');

            amountPresets.forEach(preset => {
                preset.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        customAmountContainer.style.display = 'block';
                    } else {
                        customAmountContainer.style.display = 'block';
                        amountInput.value = this.value;
                    }
                });
            });
        });
    </script>
@endpush

@push('header_css')
    <style>
        .payment-method-card {
            height: 100%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-method-card label {
            cursor: pointer;
            height: 100%;
            padding: 1rem;
        }

        .btn-check:checked+.btn-outline-primary {
            background-color: #0d6efd;
            color: white;
        }
    </style>
@endpush
