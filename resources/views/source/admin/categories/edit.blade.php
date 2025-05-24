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
                            <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Danh mục</a></li>
                            <li class="breadcrumb-item active">Chỉnh sửa danh mục</li>
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

            <div class="col-md-8">
                <!-- Form chỉnh sửa danh mục -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Chỉnh sửa danh mục</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Tên danh mục <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                    value="{{ old('name', $category->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug"
                                    value="{{ old('slug', $category->slug) }}" placeholder="Tự động tạo nếu để trống">
                                @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="parent_id">Danh mục cha</label>
                                <select class="form-control select2bs4 @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                    <option value="">-- Không có --</option>
                                    @foreach($parentCategories as $parentCategory)
                                        @if($parentCategory->id != $category->id)
                                            <option value="{{ $parentCategory->id }}"
                                                {{ (old('parent_id', $category->parent_id) == $parentCategory->id) ? 'selected' : '' }}>
                                                {{ $parentCategory->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('parent_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Mô tả</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                    rows="3">{{ old('description', $category->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="image">Hình ảnh</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror" id="image" name="image">
                                        <label class="custom-file-label" for="image">Chọn file</label>
                                    </div>
                                </div>
                                @error('image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                                @if($category->image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-thumbnail" style="max-height: 150px">
                                    <p class="text-muted small">Hình ảnh hiện tại. Tải lên ảnh mới nếu muốn thay thế.</p>
                                </div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="sort_order">Thứ tự hiển thị</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order"
                                    value="{{ old('sort_order', $category->sort_order) }}" min="0">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Trạng thái</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="status" name="status" value="active"
                                        {{ (old('status', $category->status) == 'active') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="status">
                                        <span class="text-success">Kích hoạt</span> / <span class="text-danger">Vô hiệu</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Thông tin danh mục -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin danh mục</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">ID:</dt>
                            <dd class="col-sm-8">{{ $category->id }}</dd>

                            <dt class="col-sm-4">Ngày tạo:</dt>
                            <dd class="col-sm-8">{{ $category->created_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-sm-4">Cập nhật:</dt>
                            <dd class="col-sm-8">{{ $category->updated_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-sm-4">Danh mục con:</dt>
                            <dd class="col-sm-8">{{ $category->children->count() }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Vùng nguy hiểm -->
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Vùng nguy hiểm</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-danger">Cảnh báo: Các hành động dưới đây không thể khôi phục!</p>

                        <!-- Form xóa với xác nhận -->
                        <form id="deleteForm" action="{{ route('admin.categories.destroy', $category->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash mr-2"></i>Xóa danh mục này
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
    $(function () {
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
                .replace(/\s+/g, '-')           // Thay thế khoảng trắng bằng -
                .replace(/[^\w\-]+/g, '')       // Loại bỏ tất cả các ký tự không phải chữ
                .replace(/\-\-+/g, '-')         // Thay thế nhiều - bằng một -
                .replace(/^-+/, '')             // Cắt - từ đầu văn bản
                .replace(/-+$/, '');            // Cắt - từ cuối văn bản

            $('#slug').val(slug);
        });
    });
</script>
@endpush
