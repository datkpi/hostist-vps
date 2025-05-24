@extends('layouts.admin.index')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Quản lý khách hàng</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
          <li class="breadcrumb-item active">Khách hàng</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách khách hàng</h3>
            <div class="card-tools">
              <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm khách hàng mới
              </a>
            </div>
          </div>

          <div class="card-body">
            @if (session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th style="width: 50px">#</th>
                  <th>Tên khách hàng</th>
                  <th>Email</th>
                  <th>Công ty</th>
                  <th>Số dư</th>
                  <th>Trạng thái</th>
                  <th style="width: 200px">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                @foreach($customers as $customer)
                <tr>
                  <td>{{ $customer->id }}</td>
                  <td>{{ $customer->user->name }}</td>
                  <td>{{ $customer->user->email }}</td>
                  <td>{{ $customer->company_name ?? 'N/A' }}</td>
                  <td>{{ number_format($customer->balance, 0, ',', '.') }} đ</td>
                  <td>
                    @if($customer->status == 'active')
                      <span class="badge badge-success">Hoạt động</span>
                    @else
                      <span class="badge badge-danger">Không hoạt động</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-info btn-sm">
                      <i class="fas fa-eye"></i> Xem
                    </a>
                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary btn-sm">
                      <i class="fas fa-edit"></i> Sửa
                    </a>
                    <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                        <i class="fas fa-trash"></i> Xóa
                      </button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>

            <div class="mt-3">
              {{ $customers->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
