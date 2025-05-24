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
                            <li class="breadcrumb-item active">Quản lý sản phẩm</li>
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

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách sản phẩm</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Thêm sản phẩm
                            </a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <form action="{{ route('admin.products.index') }}" method="GET" class="form-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="searchText" placeholder="Tìm theo tên sản phẩm, SKU..." value="{{ request('searchText') }}">
                                        <select name="searchBy" class="form-control">
                                            <option value="name" {{ request('searchBy') == 'name' ? 'selected' : '' }}>Tên</option>
                                            <option value="sku" {{ request('searchBy') == 'sku' ? 'selected' : '' }}>SKU</option>
                                        </select>
                                        <select name="type" class="form-control">
                                            <option value="">-- Tất cả loại --</option>
                                            <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Sản phẩm</option>
                                            <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Dịch vụ</option>
                                            <option value="ssl" {{ request('type') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                            <option value="domain" {{ request('type') == 'domain' ? 'selected' : '' }}>Tên miền</option>
                                            <option value="hosting" {{ request('type') == 'hosting' ? 'selected' : '' }}>Hosting</option>
                                        </select>
                                        <select name="category_id" class="form-control">
                                            <option value="">-- Tất cả danh mục --</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-right">
                                <select id="sort-by" class="form-control" onchange="sortProducts(this.value)">
                                    <option value="id-desc" {{ request('sortBy') == 'id' && request('orderBy') == 'desc' ? 'selected' : '' }}>Mới nhất</option>
                                    <option value="id-asc" {{ request('sortBy') == 'id' && request('orderBy') == 'asc' ? 'selected' : '' }}>Cũ nhất</option>
                                    <option value="name-asc" {{ request('sortBy') == 'name' && request('orderBy') == 'asc' ? 'selected' : '' }}>Tên A-Z</option>
                                    <option value="name-desc" {{ request('sortBy') == 'name' && request('orderBy') == 'desc' ? 'selected' : '' }}>Tên Z-A</option>
                                    <option value="price-asc" {{ request('sortBy') == 'price' && request('orderBy') == 'asc' ? 'selected' : '' }}>Giá tăng dần</option>
                                    <option value="price-desc" {{ request('sortBy') == 'price' && request('orderBy') == 'desc' ? 'selected' : '' }}>Giá giảm dần</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Loại</th>
                                        <th>Giá</th>
                                        <th>Trạng thái</th>
                                        <th style="width: 120px">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td class="text-center">
                                            @if($product->image)
                                                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-height: 50px; max-width: 50px;">
                                            @else
                                                <span class="text-muted">Không có</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            @if($product->sku)
                                                <br><small class="text-muted">SKU: {{ $product->sku }}</small>
                                            @endif
                                            @if($product->is_featured)
                                                <span class="badge badge-success ml-1">Nổi bật</span>
                                            @endif
                                        </td>
                                        <td>{{ $product->category ? $product->category->name : 'Không có' }}</td>
                                        <td>
                                            @switch($product->type)
                                                @case('product')
                                                    <span class="badge badge-primary">Sản phẩm</span>
                                                    @break
                                                @case('service')
                                                    <span class="badge badge-info">Dịch vụ</span>
                                                    @break
                                                @case('ssl')
                                                    <span class="badge badge-success">SSL</span>
                                                    @break
                                                @case('domain')
                                                    <span class="badge badge-warning">Tên miền</span>
                                                    @break
                                                @case('hosting')
                                                    <span class="badge badge-secondary">Hosting</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-dark">Khác</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="text-primary">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                            @if($product->sale_price)
                                                <br><span class="text-danger">{{ number_format($product->sale_price, 0, ',', '.') }} đ</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($product->product_status)
                                                @case('active')
                                                    <span class="badge badge-success">Kích hoạt</span>
                                                    @break
                                                @case('inactive')
                                                    <span class="badge badge-danger">Vô hiệu</span>
                                                    @break
                                                @case('draft')
                                                    <span class="badge badge-secondary">Bản nháp</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-info">{{ $product->product_status }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Không có dữ liệu</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer clearfix">
                        {{ $products->appends(request()->all())->links() }}
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script>
    // Hàm sắp xếp sản phẩm
    function sortProducts(value) {
        const params = new URLSearchParams(window.location.search);
        const [sortBy, orderBy] = value.split('-');

        params.set('sortBy', sortBy);
        params.set('orderBy', orderBy);

        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
</script>
@endpush
