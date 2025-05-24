@extends('layouts.admin.index')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Quản lý hoàn tiền</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Quản lý hoàn tiền</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách yêu cầu hoàn tiền</h3>
                <div class="card-tools">
                    <form action="{{ route('cashback.index') }}" method="GET" class="form-inline">
                        <div class="input-group">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Đã xử lý</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Số tiền</th>
                                <th>Thông tin chuyển khoản</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashbacks as $cashback)
                                <tr>
                                    <td>{{ $cashback->id }}</td>
                                    <td>
                                        #{{ $cashback->order->order_number }}<br>
                                        <small>{{ number_format($cashback->order->total_amount, 0, ',', '.') }} đ</small>
                                    </td>
                                    <td>
                                        {{ $cashback->customer->name }}<br>
                                        <small>{{ $cashback->customer->email }}</small><br>
                                        <small>{{ $cashback->customer->phone }}</small>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($cashback->amount, 0, ',', '.') }} đ
                                    </td>
                                    <td>
                                        <strong>{{ $cashback->bank_name }}</strong><br>
                                        <strong>STK:</strong> {{ $cashback->account_number }}<br>
                                        <strong>CTK:</strong> {{ $cashback->account_holder }}<br>
                                        @if($cashback->branch)
                                            <strong>CN:</strong> {{ $cashback->branch }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($cashback->status == 'pending')
                                            <span class="badge badge-warning">Chờ duyệt</span>
                                        @elseif($cashback->status == 'approved')
                                            <span class="badge badge-primary">Đã duyệt</span>
                                        @elseif($cashback->status == 'processed')
                                            <span class="badge badge-success">Đã xử lý</span>
                                        @elseif($cashback->status == 'rejected')
                                            <span class="badge badge-danger">Đã từ chối</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($cashback->status == 'pending')
                                            <form action="{{ route('cashback.approve', $cashback->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Xác nhận duyệt yêu cầu hoàn tiền này?')">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            <form action="{{ route('cashback.reject', $cashback->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="admin_note" value="Yêu cầu hoàn tiền không hợp lệ">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận từ chối yêu cầu hoàn tiền này?')">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            </form>
                                        @elseif($cashback->status == 'approved')
                                            <form action="{{ route('cashback.process', $cashback->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Xác nhận đã chuyển khoản cho yêu cầu này?')">
                                                    <i class="fas fa-check-double"></i> Xác nhận đã chuyển
                                                </button>
                                            </form>
                                        @elseif($cashback->status == 'processed')
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i> Hoàn thành
                                                @if($cashback->processed_at)
                                                    <br><small>{{ $cashback->processed_at->format('d/m/Y') }}</small>
                                                @endif
                                            </span>
                                        @elseif($cashback->status == 'rejected')
                                            <span class="text-danger">
                                                <i class="fas fa-ban"></i> Đã từ chối
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Không có yêu cầu hoàn tiền nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $cashbacks->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
