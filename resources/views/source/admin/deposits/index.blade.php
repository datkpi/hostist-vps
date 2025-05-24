@extends('layouts.admin.index')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Quản lý yêu cầu nạp tiền</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Yêu cầu nạp tiền</li>
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Danh sách yêu cầu nạp tiền</h3>

                            <div class="card-tools">
                                <div class="btn-group">
                                    <a href="{{ route('deposits.index', ['status' => 'all']) }}"
                                        class="btn btn-sm {{ $status == 'all' ? 'btn-primary' : 'btn-default' }}">
                                        Tất cả <span class="badge badge-light">{{ $counts['all'] }}</span>
                                    </a>
                                    <a href="{{ route('deposits.index', ['status' => 'pending']) }}"
                                        class="btn btn-sm {{ $status == 'pending' ? 'btn-primary' : 'btn-default' }}">
                                        Chờ xử lý <span class="badge badge-warning">{{ $counts['pending'] }}</span>
                                    </a>
                                    <a href="{{ route('deposits.index', ['status' => 'approved']) }}"
                                        class="btn btn-sm {{ $status == 'approved' ? 'btn-primary' : 'btn-default' }}">
                                        Đã xác nhận <span class="badge badge-success">{{ $counts['approved'] }}</span>
                                    </a>
                                    <a href="{{ route('deposits.index', ['status' => 'rejected']) }}"
                                        class="btn btn-sm {{ $status == 'rejected' ? 'btn-primary' : 'btn-default' }}">
                                        Đã từ chối <span class="badge badge-danger">{{ $counts['rejected'] }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Mã giao dịch</th>
                                            <th>Khách hàng</th>
                                            <th>Số tiền</th>
                                            <th>Phương thức</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày yêu cầu</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($deposits as $deposit)
                                            <tr>
                                                <td>{{ $deposit->transaction_code }}</td>
                                                <td>
                                                    @if ($deposit->customer && $deposit->customer->user)
                                                        {{ $deposit->customer->user->name }}<br>
                                                        <small>{{ $deposit->customer->user->email }}</small>
                                                    @else
                                                        <span class="text-muted">Không có thông tin</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($deposit->amount, 0, ',', '.') }} đ</td>
                                                <td>
                                                    @if ($deposit->payment_method == 'bank')
                                                        <span class="badge badge-info">Chuyển khoản ngân hàng</span>
                                                    @elseif($deposit->payment_method == 'momo')
                                                        <span class="badge badge-primary">Ví MoMo</span>
                                                    @elseif($deposit->payment_method == 'zalopay')
                                                        <span class="badge badge-primary">ZaloPay</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($deposit->status == 'pending')
                                                        <span class="badge badge-warning">Chờ xử lý</span>
                                                    @elseif($deposit->status == 'approved')
                                                        <span class="badge badge-success">Đã xác nhận</span>
                                                    @elseif($deposit->status == 'rejected')
                                                        <span class="badge badge-danger">Đã từ chối</span>
                                                    @endif
                                                </td>
                                                <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('deposits.show', $deposit->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Chi tiết
                                                    </a>

                                                    @if ($deposit->status == 'pending')
                                                        <button type="button" class="btn btn-sm btn-success"
                                                            data-toggle="modal"
                                                            data-target="#approveModal{{ $deposit->id }}">
                                                            <i class="fas fa-check"></i> Xác nhận
                                                        </button>

                                                        <button type="button" class="btn btn-sm btn-danger"
                                                            data-toggle="modal"
                                                            data-target="#rejectModal{{ $deposit->id }}">
                                                            <i class="fas fa-times"></i> Từ chối
                                                        </button>

                                                        <!-- Modal Xác nhận -->
                                                        <div class="modal fade" id="approveModal{{ $deposit->id }}"
                                                            tabindex="-1" role="dialog"
                                                            aria-labelledby="approveModalLabel{{ $deposit->id }}"
                                                            aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title"
                                                                            id="approveModalLabel{{ $deposit->id }}">Xác
                                                                            nhận nạp tiền</h5>
                                                                        <button type="button" class="close"
                                                                            data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <form
                                                                        action="{{ route('deposits.approve', $deposit->id) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        <div class="modal-body">
                                                                            <p>Bạn có chắc chắn muốn xác nhận yêu cầu nạp
                                                                                tiền này?</p>
                                                                            <p><strong>Mã giao dịch:</strong>
                                                                                {{ $deposit->transaction_code }}</p>
                                                                            <p><strong>Khách hàng:</strong>
                                                                                {{ $deposit->customer->user->name ?? 'Không có thông tin' }}
                                                                            </p>
                                                                            <p><strong>Số tiền:</strong>
                                                                                {{ number_format($deposit->amount, 0, ',', '.') }}
                                                                                đ</p>
                                                                            <p class="text-success">Số tiền này sẽ được cộng
                                                                                vào tài khoản của khách hàng.</p>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary"
                                                                                data-dismiss="modal">Hủy</button>
                                                                            <button type="submit"
                                                                                class="btn btn-success">Xác nhận</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Modal Từ chối -->
                                                        <div class="modal fade" id="rejectModal{{ $deposit->id }}"
                                                            tabindex="-1" role="dialog"
                                                            aria-labelledby="rejectModalLabel{{ $deposit->id }}"
                                                            aria-hidden="true">
                                                            <div class="modal-dialog" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title"
                                                                            id="rejectModalLabel{{ $deposit->id }}">Từ
                                                                            chối yêu cầu nạp tiền</h5>
                                                                        <button type="button" class="close"
                                                                            data-dismiss="modal" aria-label="Close">
                                                                            <span aria-hidden="true">&times;</span>
                                                                        </button>
                                                                    </div>
                                                                    <form
                                                                        action="{{ route('deposits.reject', $deposit->id) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        <div class="modal-body">
                                                                            <p>Bạn có chắc chắn muốn từ chối yêu cầu nạp
                                                                                tiền này?</p>
                                                                            <p><strong>Mã giao dịch:</strong>
                                                                                {{ $deposit->transaction_code }}</p>
                                                                            <p><strong>Khách hàng:</strong>
                                                                                {{ $deposit->customer->user->name ?? 'Không có thông tin' }}
                                                                            </p>
                                                                            <p><strong>Số tiền:</strong>
                                                                                {{ number_format($deposit->amount, 0, ',', '.') }}
                                                                                đ</p>

                                                                            <div class="form-group">
                                                                                <label for="reason">Lý do từ chối <span
                                                                                        class="text-danger">*</span></label>
                                                                                <input type="text" class="form-control"
                                                                                    id="reason" name="reason"
                                                                                    required>
                                                                                <small class="form-text text-muted">Lý do
                                                                                    này sẽ được gửi đến khách hàng.</small>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button"
                                                                                class="btn btn-secondary"
                                                                                data-dismiss="modal">Hủy</button>
                                                                            <button type="submit"
                                                                                class="btn btn-danger">Từ chối</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Không có yêu cầu nạp tiền nào</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $deposits->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="row mt-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ number_format($stats['today_deposits'] ?? 0, 0, ',', '.') }} đ</h3>
                            <p>Nạp tiền hôm nay</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ number_format($stats['total_approved'] ?? 0, 0, ',', '.') }} đ</h3>
                            <p>Tổng tiền đã xác nhận</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ number_format($stats['total_pending'] ?? 0, 0, ',', '.') }} đ</h3>
                            <p>Đang chờ xử lý</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ number_format($stats['total_rejected'] ?? 0, 0, ',', '.') }} đ</h3>
                            <p>Đã từ chối</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ thống kê -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Thống kê nạp tiền 7 ngày gần đây</h3>

                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="chart">
                                <canvas id="depositChart"
                                    style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Dữ liệu từ controller
                var depositData = @json($chart_data ?? []);

                // Chuẩn bị dữ liệu cho biểu đồ
                var labels = depositData.map(function(item) {
                    return item.date;
                });
                var approvedData = depositData.map(function(item) {
                    return item.approved;
                });
                var pendingData = depositData.map(function(item) {
                    return item.pending;
                });
                var rejectedData = depositData.map(function(item) {
                    return item.rejected;
                });

                // Tạo biểu đồ
                var ctx = document.getElementById('depositChart').getContext('2d');
                var depositChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Đã xác nhận',
                                data: approvedData,
                                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Chờ xử lý',
                                data: pendingData,
                                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                                borderColor: 'rgba(255, 193, 7, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Đã từ chối',
                                data: rejectedData,
                                backgroundColor: 'rgba(220, 53, 69, 0.8)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                stacked: false
                            },
                            y: {
                                stacked: false,
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") +
                                            ' đ';
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.dataset.label || '';
                                        var value = context.raw || 0;
                                        value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                                        return label + ': ' + value + ' đ';
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
