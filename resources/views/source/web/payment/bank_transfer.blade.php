@extends('layouts.web.default')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fa fa-check-circle me-2"></i>Đơn hàng đã được tạo thành công</h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fa fa-file-invoice-dollar fa-4x text-success"></i>
                            </div>
                            <h4>Cảm ơn bạn đã đặt hàng!</h4>
                            <p>Vui lòng hoàn tất thanh toán theo hướng dẫn dưới đây.</p>
                        </div>

                        <div class="alert alert-info">
                            <p class="mb-0"><i class="fa fa-info-circle me-2"></i>Hệ thống đã kiểm tra số dư tài khoản của
                                bạn không đủ để thanh toán đơn hàng này. Vui lòng chuyển khoản theo thông tin dưới đây.</p>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Thông tin thanh toán</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Mã hóa đơn:</span>
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Mã đơn hàng:</span>
                                                <strong>{{ $invoice->order->order_number }}</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Trạng thái:</span>
                                                <strong class="text-warning">Chờ thanh toán</strong>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Số tiền cần thanh toán:</span>
                                                <strong class="text-danger">{{ number_format($amountToPay, 0, ',', '.') }}
                                                    đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Ngày tạo:</span>
                                                <strong>{{ $invoice->created_at->format('d/m/Y H:i') }}</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Ngày hết hạn:</span>
                                                <strong>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</strong>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thêm phần hiển thị thông tin dịch vụ và domain -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Thông tin dịch vụ</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Dịch vụ</th>
                                                <th>Thời hạn</th>
                                                <th>Domain</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoice->order->items as $item)
                                                @php
                                                    $options = json_decode($item->options, true) ?: [];
                                                    $period = $options['period'] ?? $item->duration ?? 1;
                                                    $domain = $item->domain ?? ($options['domain'] ?? 'N/A');

                                                    // Kiểm tra xem item có phải là SSL hoặc domain không
                                                    $isSSLorDomain = $item->product && ($item->product->type == 'ssl' || $item->product->type == 'domain');
                                                @endphp
                                                <tr>
                                                    <td>{{ $item->name }}</td>
                                                    <td>{{ $period }} năm</td>
                                                    <td>
                                                        @if($isSSLorDomain && $domain != 'N/A')
                                                            <span class="badge bg-info text-white">{{ $domain }}</span>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($item->subtotal, 0, ',', '.') }} đ</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Tổng cộng:</th>
                                                <th class="text-end">{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Thông tin chuyển khoản</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Thông tin ngân hàng</h6>
                                        <div class="payment-info">
                                            <p><strong>Tên ngân hàng:</strong> {{ $config->company_bank_name ?? 'ACB' }}</p>
                                            <p><strong>Số tài khoản:</strong>
                                                {{ $config->company_bank_account_number ?? '24768' }}</p>
                                            <p><strong>Chủ tài khoản:</strong>
                                                {{ $config->company_bank_account_name ?? 'Company Name' }}</p>
                                            <p><strong>Chi nhánh:</strong>
                                                {{ $config->company_bank_branch ?? 'Chi nhánh Center' }}</p>
                                            <p><strong>Nội dung CK:</strong> <span
                                                    class="text-danger">{{ "ThanhToan{$invoice->invoice_number}" }}</span>
                                            </p>
                                            <p><strong>Số tiền:</strong> <span
                                                    class="text-danger">{{ number_format($amountToPay, 0, ',', '.') }}
                                                    đ</span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        <h6 class="mb-3">QR Code thanh toán</h6>
                                        <!-- QR code image -->
                                        @if ($config && $config->company_bank_qr_code)
                                            <img src="{{ asset('storage/' . $config->company_bank_qr_code) }}"
                                                alt="QR Code thanh toán" class="img-fluid" style="max-width: 200px;">
                                        @else
                                            <img src="{{ asset('images/qr-placeholder.png') }}" alt="QR Code"
                                                style="max-width: 200px;" class="img-fluid">
                                        @endif
                                        <p class="mt-2 small">Quét mã QR để thanh toán nhanh chóng</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <p class="mb-1"><i class="fa fa-exclamation-triangle me-2"></i><strong>Lưu ý quan
                                    trọng:</strong></p>
                            <ul class="mb-0">
                                <li>Vui lòng sử dụng đúng nội dung chuyển khoản để hệ thống có thể xác nhận thanh toán nhanh
                                    chóng.</li>
                                <li>Thời gian xác nhận thanh toán có thể mất từ 1-24 giờ làm việc sau khi bạn chuyển khoản.
                                </li>
                                <li>Sau khi thanh toán thành công, trạng thái đơn hàng của bạn sẽ được cập nhật.</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('customer.invoices') }}" class="btn btn-outline-primary">
                                <i class="fa fa-list me-2"></i>Xem hóa đơn chưa thanh toán
                            </a>
                            <a href="{{ route('customer.orders') }}" class="btn btn-primary">
                                <i class="fa fa-shopping-cart me-2"></i>Xem đơn hàng của tôi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
