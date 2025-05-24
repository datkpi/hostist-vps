@extends('layouts.admin.index')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Chỉnh sửa thông tin khách hàng</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Khách hàng</a></li>
          <li class="breadcrumb-item active">Chỉnh sửa</li>
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
            <h3 class="card-title">Thông tin khách hàng: {{ $customer->user->name }}</h3>
          </div>

          <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
              @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif

              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="name">Tên khách hàng <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $customer->user->name) }}" required>
                  </div>

                  <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $customer->user->email) }}" required>
                  </div>

                  <div class="form-group">
                    <label for="password">Mật khẩu (để trống nếu không thay đổi)</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="form-text text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                  </div>

                  <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $customer->user->phone) }}">
                  </div>

                  <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $customer->user->address) }}</textarea>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="company_name">Tên công ty</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name) }}">
                  </div>

                  <div class="form-group">
                    <label for="tax_code">Mã số thuế</label>
                    <input type="text" class="form-control" id="tax_code" name="tax_code" value="{{ old('tax_code', $customer->tax_code) }}">
                  </div>

                  <div class="form-group">
                    <label for="business_type">Loại hình kinh doanh</label>
                    <select class="form-control select2" id="business_type" name="business_type">
                      <option value="">-- Chọn loại hình --</option>
                      <option value="individual" {{ old('business_type', $customer->business_type) == 'individual' ? 'selected' : '' }}>Cá nhân</option>
                      <option value="company" {{ old('business_type', $customer->business_type) == 'company' ? 'selected' : '' }}>Doanh nghiệp</option>
                      <option value="organization" {{ old('business_type', $customer->business_type) == 'organization' ? 'selected' : '' }}>Tổ chức</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="website">Website</label>
                    <input type="url" class="form-control" id="website" name="website" value="{{ old('website', $customer->website) }}">
                  </div>

                  <div class="form-group">
                    <label for="status">Trạng thái <span class="text-danger">*</span></label>
                    <select class="form-control" id="status" name="status" required>
                      <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                      <option value="inactive" {{ old('status', $customer->status) == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="card card-primary">
                    <div class="card-header">
                      <h3 class="card-title">Thông tin tài chính</h3>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="balance">Số dư tài khoản</label>
                            <div class="input-group">
                              <input type="text" class="form-control" id="balance" name="balance" value="{{ old('balance', $customer->balance) }}">
                              <div class="input-group-append">
                                <span class="input-group-text">VNĐ</span>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label for="wallet_id">ID Ví điện tử (nếu có)</label>
                            <input type="text" class="form-control" id="wallet_id" name="wallet_id" value="{{ old('wallet_id', $customer->wallet_id) }}">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
              <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Quay lại</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('js')
<script>
  $(function () {
    //Initialize Select2 Elements
    $('.select2').select2();
  });
</script>
@endpush
