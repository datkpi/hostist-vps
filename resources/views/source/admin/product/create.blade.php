@extends('layouts.admin.index')

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <!-- Breadcrumb -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Sản phẩm</a></li>
                                <li class="breadcrumb-item active">Thêm sản phẩm mới</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <!-- Thông báo lỗi -->
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Thông báo thành công -->
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                </div>

                <div class="col-md-9">
                    <!-- Form tạo sản phẩm mới -->
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cơ bản</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                        id="slug" name="slug" value="{{ old('slug') }}"
                                        placeholder="Tự động tạo nếu để trống">
                                    @error('slug')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="sku">Mã sản phẩm (SKU)</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                        id="sku" name="sku" value="{{ old('sku') }}">
                                    @error('sku')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="short_description">Mô tả ngắn</label>
                                    <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description"
                                        name="short_description" rows="3">{{ old('short_description') }}</textarea>
                                    @error('short_description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Mô tả chi tiết</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="5">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card card-info">
                            <div class="card-header">
                                <h3 class="card-title">Giá & Kho hàng</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="price">Giá bán <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">VNĐ</span>
                                                </div>
                                                <input type="number"
                                                    class="form-control @error('price') is-invalid @enderror" id="price"
                                                    name="price" value="{{ old('price', 0) }}" min="0"
                                                    step="1000" required>
                                                @error('price')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sale_price">Giá khuyến mãi</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">VNĐ</span>
                                                </div>
                                                <input type="number"
                                                    class="form-control @error('sale_price') is-invalid @enderror"
                                                    id="sale_price" name="sale_price" value="{{ old('sale_price') }}"
                                                    min="0" step="1000">
                                                @error('sale_price')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="stock">Số lượng trong kho</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                        id="stock" name="stock" value="{{ old('stock', -1) }}" min="-1">
                                    <small class="form-text text-muted">Nhập -1 nếu không giới hạn số lượng</small>
                                    @error('stock')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Dịch vụ định kỳ</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="is_recurring"
                                            name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                                            onchange="toggleRecurringOptions()">
                                        <label class="custom-control-label" for="is_recurring">Đây là dịch vụ định
                                            kỳ</label>
                                    </div>
                                </div>

                                <div id="recurring_options"
                                    style="display: {{ old('is_recurring') ? 'block' : 'none' }};">
                                    <div class="form-group">
                                        <label for="recurring_period">Chu kỳ (tháng)</label>
                                        <input type="number"
                                            class="form-control @error('recurring_period') is-invalid @enderror"
                                            id="recurring_period" name="recurring_period"
                                            value="{{ old('recurring_period', 1) }}" min="1">
                                        @error('recurring_period')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="auto_renew"
                                                name="auto_renew" value="1"
                                                {{ old('auto_renew') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="auto_renew">Tự động gia hạn</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Dữ liệu bổ sung</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="meta_data">Dữ liệu Meta</label>
                                    <textarea class="form-control @error('meta_data') is-invalid @enderror" id="meta_data" name="meta_data"
                                        rows="3">{{ old('meta_data') }}</textarea>
                                    <small class="form-text text-muted">Nhập dữ liệu meta (có thể chứa các khóa private,
                                        chứng chỉ SSL...)</small>
                                    @error('meta_data')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="options">Tùy chọn</label>
                                    <textarea class="form-control @error('options') is-invalid @enderror" id="options" name="options" rows="3">{{ old('options') }}</textarea>
                                    <small class="form-text text-muted">Nhập các tùy chọn bổ sung</small>
                                    @error('options')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Phân loại & Trạng thái</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="category_id">Danh mục</label>
                                <select class="form-control select2bs4 @error('category_id') is-invalid @enderror"
                                    id="category_id" name="category_id" style="width: 100%;">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="parent_product_id">Sản phẩm cha</label>
                                <select class="form-control select2bs4 @error('parent_product_id') is-invalid @enderror"
                                    id="parent_product_id" name="parent_product_id" style="width: 100%;">
                                    <option value="">-- Không có --</option>
                                    @foreach ($parentProducts as $product)
                                        <option value="{{ $product->id }}"
                                            {{ old('parent_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Chọn nếu đây là biến thể của một sản phẩm khác</small>
                                @error('parent_product_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="type">Loại sản phẩm <span class="text-danger">*</span></label>
                                <select class="form-control @error('type') is-invalid @enderror" id="type"
                                    name="type" required>
                                    <option value="product" {{ old('type') == 'product' ? 'selected' : '' }}>Sản phẩm
                                    </option>
                                    <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Dịch vụ
                                    </option>
                                    <option value="ssl" {{ old('type') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="domain" {{ old('type') == 'domain' ? 'selected' : '' }}>Tên miền
                                    </option>
                                    <option value="hosting" {{ old('type') == 'hosting' ? 'selected' : '' }}>Hosting
                                    </option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="product_status">Trạng thái</label>
                                <select class="form-control @error('product_status') is-invalid @enderror"
                                    id="product_status" name="product_status">
                                    <option value="active"
                                        {{ old('product_status', 'active') == 'active' ? 'selected' : '' }}>Kích hoạt
                                    </option>
                                    <option value="inactive" {{ old('product_status') == 'inactive' ? 'selected' : '' }}>
                                        Vô hiệu</option>
                                    <option value="draft" {{ old('product_status') == 'draft' ? 'selected' : '' }}>Bản
                                        nháp</option>
                                </select>
                                @error('product_status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="sort_order">Thứ tự hiển thị</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                    id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                    min="0">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_featured"
                                        name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_featured">Sản phẩm nổi bật</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Hình ảnh</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="image">Hình ảnh chính</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file"
                                            class="custom-file-input @error('image') is-invalid @enderror" id="image"
                                            name="image">
                                        <label class="custom-file-label" for="image">Chọn file</label>
                                    </div>
                                </div>
                                @error('image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="preview-image mt-3 text-center" style="display: none;">
                                <img id="image-preview" src="#" alt="Xem trước hình ảnh"
                                    class="img-fluid img-thumbnail" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary btn-block">Thêm sản phẩm</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-block">Hủy</a>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('js')
    @push('js')
        <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
        <script>
            $(function() {
                // Các code khác...

                // CKEditor cho mô tả chi tiết - không kèm upload ảnh
                CKEDITOR.replace('description');
            });
        </script>
    @endpush
    <script>
        $(function() {
            // Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            // Custom file input
            bsCustomFileInput.init();

            // Tự động tạo slug từ tên
            $('#name').on('input', function() {
                var name = $(this).val();
                var slug = name.toLowerCase()
                    .replace(/\s+/g, '-') // Thay thế khoảng trắng bằng -
                    .replace(/[^\w\-]+/g, '') // Loại bỏ tất cả các ký tự không phải chữ
                    .replace(/\-\-+/g, '-') // Thay thế nhiều - bằng một -
                    .replace(/^-+/, '') // Cắt - từ đầu văn bản
                    .replace(/-+$/, ''); // Cắt - từ cuối văn bản

                $('#slug').val(slug);
            });

            // Xem trước hình ảnh
            $("#image").change(function() {
                readURL(this);
            });

            // CKEditor cho mô tả chi tiết
            if (typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('description');
            }
        });

        // Hàm xem trước hình ảnh
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#image-preview').attr('src', e.target.result);
                    $('.preview-image').show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Hàm hiển thị/ẩn tùy chọn định kỳ
        function toggleRecurringOptions() {
            if ($('#is_recurring').is(':checked')) {
                $('#recurring_options').show();
            } else {
                $('#recurring_options').hide();
            }
        }
    </script>
@endpush
