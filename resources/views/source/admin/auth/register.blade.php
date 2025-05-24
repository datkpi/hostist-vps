
@extends('layouts.admin.default')

@section('content')
<div class="register-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="register-form">
                    <div class="register-logo">
                        <h2 class="text-primary">Đăng ký tài khoản</h2>
                    </div>

                    @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Họ tên</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                       name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                       value="{{ old('phone') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Tên công ty (nếu có)</label>
                                <input type="text" class="form-control" id="company_name" name="company_name"
                                       value="{{ old('company_name') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="2">{{ old('address') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tax_code" class="form-label">Mã số thuế (nếu có)</label>
                                <input type="text" class="form-control" id="tax_code" name="tax_code"
                                       value="{{ old('tax_code') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Website (nếu có)</label>
                                <input type="url" class="form-control" id="website" name="website"
                                       value="{{ old('website') }}">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Đăng ký</button>
                        </div>

                        <div class="mt-3 text-center">
                            <p>Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
