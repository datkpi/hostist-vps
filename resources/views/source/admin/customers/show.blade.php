@extends('layouts.admin.index')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Thông tin khách hàng</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Khách hàng</a></li>
                        <li class="breadcrumb-item active">Thông tin chi tiết</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <!-- Thông tin khách hàng -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle"
                                    src="{{ $customer->user->avatar ? Storage::url($customer->user->avatar) : asset('assets/admin/img/default-avatar.png') }}"
                                    alt="User profile picture">
                            </div>

                            <h3 class="profile-username text-center">{{ $customer->user->name }}</h3>
                            <p class="text-muted text-center">{{ $customer->company_name ?? 'Khách hàng cá nhân' }}</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Email</b> <a class="float-right">{{ $customer->user->email }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Điện thoại</b> <a
                                        class="float-right">{{ $customer->user->phone ?? 'Chưa cung cấp' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Mã số thuế</b> <a
                                        class="float-right">{{ $customer->tax_code ?? 'Chưa cung cấp' }}</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Trạng thái</b>
                                    <a class="float-right">
                                        @if ($customer->status == 'active')
                                            <span class="badge badge-success">Hoạt động</span>
                                        @else
                                            <span class="badge badge-danger">Không hoạt động</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>

                            <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                class="btn btn-primary btn-block"><i class="fas fa-edit"></i> Chỉnh sửa thông tin</a>
                        </div>
                    </div>

                    <!-- Thông tin tài chính -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin tài chính</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Số dư tài khoản</b>
                                    <a class="float-right">{{ number_format($customer->balance, 0, ',', '.') }} đ</a>
                                </li>
                                <li class="list-group-item">
                                    <b>Wallet ID</b>
                                    <a class="float-right">{{ $customer->wallet_id ?? 'Chưa có' }}</a>
                                </li>
                            </ul>

                            <button type="button" class="btn btn-success btn-block mb-2" data-toggle="modal"
                                data-target="#adjustBalanceModal">
                                <i class="fas fa-money-bill-wave"></i> Điều chỉnh số dư
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Nav tabs -->
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#basic-info" data-toggle="tab">Thông
                                        tin cơ bản</a></li>
                                <li class="nav-item"><a class="nav-link" href="#orders" data-toggle="tab">Đơn hàng</a></li>
                                <li class="nav-item"><a class="nav-link" href="#invoices" data-toggle="tab">Hóa đơn</a></li>
                                <li class="nav-item"><a class="nav-link" href="#services" data-toggle="tab">Dịch vụ</a></li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Thông tin cơ bản -->
                                <div class="active tab-pane" id="basic-info">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Họ tên:</label>
                                                <p>{{ $customer->user->name }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Email:</label>
                                                <p>{{ $customer->user->email }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Số điện thoại:</label>
                                                <p>{{ $customer->user->phone ?? 'Chưa cung cấp' }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Địa chỉ:</label>
                                                <p>{{ $customer->user->address ?? 'Chưa cung cấp' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tên công ty:</label>
                                                <p>{{ $customer->company_name ?? 'Chưa cung cấp' }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Mã số thuế:</label>
                                                <p>{{ $customer->tax_code ?? 'Chưa cung cấp' }}</p>
                                            </div>
                                            <div class="form-group">
                                                <label>Loại hình kinh doanh:</label>
                                                <p>
                                                    @if ($customer->business_type == 'individual')
                                                        Cá nhân
                                                    @elseif($customer->business_type == 'company')
                                                        Doanh nghiệp
                                                    @elseif($customer->business_type == 'organization')
                                                        Tổ chức
                                                    @else
                                                        Chưa cung cấp
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="form-group">
                                                <label>Website:</label>
                                                <p>
                                                    @if ($customer->website)
                                                        <a href="{{ $customer->website }}"
                                                            target="_blank">{{ $customer->website }}</a>
                                                    @else
                                                        Chưa cung cấp
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Đơn hàng -->
                                <!-- Đơn hàng -->
                                <div class="tab-pane" id="orders">
                                    @if ($customer->orders && $customer->orders->count() > 0)
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Mã đơn hàng</th>
                                                    <th>Ngày đặt</th>
                                                    <th>Trạng thái</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($customer->orders as $order)
                                                    <tr>
                                                        <td>{{ $order->order_number }}</td>
                                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                                        <td>
                                                            @if ($order->status == 'pending')
                                                                <span class="badge badge-warning">Đang xử lý</span>
                                                            @elseif($order->status == 'processing')
                                                                <span class="badge badge-info">Đang xử lý</span>
                                                            @elseif($order->status == 'completed')
                                                                <span class="badge badge-success">Hoàn thành</span>
                                                            @elseif($order->status == 'cancelled')
                                                                <span class="badge badge-danger">Đã hủy</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                                        <td>
                                                            <a href="#" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i> Xem
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-center">Khách hàng chưa có đơn hàng nào.</p>
                                    @endif
                                </div>

                                <!-- Hóa đơn -->
                                <div class="tab-pane" id="invoices">
                                    @if (isset($invoices) && $invoices->count() > 0)
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Mã hóa đơn</th>
                                                    <th>Ngày tạo</th>
                                                    <th>Ngày hết hạn</th>
                                                    <th>Trạng thái</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $invoice)
                                                    <tr>
                                                        <td>{{ $invoice->invoice_number }}</td>
                                                        <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                                        <td>{{ $invoice->due_date ? date('d/m/Y', strtotime($invoice->due_date)) : 'N/A' }}
                                                        </td>
                                                        <td>
                                                            @if ($invoice->status == 'draft')
                                                                <span class="badge badge-secondary">Nháp</span>
                                                            @elseif($invoice->status == 'sent')
                                                                <span class="badge badge-info">Đã gửi</span>
                                                            @elseif($invoice->status == 'paid')
                                                                <span class="badge badge-success">Đã thanh toán</span>
                                                            @elseif($invoice->status == 'overdue')
                                                                <span class="badge badge-danger">Quá hạn</span>
                                                            @elseif($invoice->status == 'cancelled')
                                                                <span class="badge badge-dark">Đã hủy</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ number_format($invoice->total_amount, 0, ',', '.') }} đ</td>
                                                        <td>
                                                            <a href="#" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i> Xem
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-center">Khách hàng chưa có hóa đơn nào.</p>
                                    @endif
                                </div>
                                <!-- Dịch vụ -->
                                <div class="tab-pane" id="services">
                                    @if ($customer->services && $customer->services->count() > 0)
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Dịch vụ</th>
                                                    <th>Ngày bắt đầu</th>
                                                    <th>Ngày kết thúc</th>
                                                    <th>Trạng thái</th>
                                                    <th>Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($customer->services as $service)
                                                    <tr>
                                                        <td>{{ $service->name }}</td>
                                                        <td>{{ $service->start_date ? date('d/m/Y', strtotime($service->start_date)) : 'N/A' }}
                                                        </td>
                                                        <td>{{ $service->end_date ? date('d/m/Y', strtotime($service->end_date)) : 'N/A' }}
                                                        </td>
                                                        <td>
                                                            @if ($service->status == 'active')
                                                                <span class="badge badge-success">Hoạt động</span>
                                                            @elseif($service->status == 'inactive')
                                                                <span class="badge badge-danger">Không hoạt động</span>
                                                            @elseif($service->status == 'pending')
                                                                <span class="badge badge-warning">Đang xử lý</span>
                                                            @elseif($service->status == 'expired')
                                                                <span class="badge badge-secondary">Hết hạn</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="#" class="btn btn-info btn-sm">
                                                                <i class="fas fa-eye"></i> Xem
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-center">Khách hàng chưa có dịch vụ nào.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal điều chỉnh số dư -->
    <div class="modal fade" id="adjustBalanceModal" tabindex="-1" role="dialog"
        aria-labelledby="adjustBalanceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.customers.adjustBalance', $customer->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="adjustBalanceModalLabel">Điều chỉnh số dư tài khoản</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="type">Hình thức</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="add">Cộng tiền</option>
                                <option value="subtract">Trừ tiền</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="amount">Số tiền</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="amount" name="amount" min="0"
                                    required>
                                <div class="input-group-append">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="note">Ghi chú</label>
                            <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thực hiện</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            // Bổ sung script nếu cần
        });
    </script>
@endpush
