<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\ProductsRepository;
use App\Repositories\CategoriesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    protected $productsRepository;
    protected $categoriesRepository;

    /**
     * Khởi tạo controller
     *
     * @param ProductsRepository $productsRepository
     * @param CategoriesRepository $categoriesRepository
     */
    public function __construct(
        ProductsRepository $productsRepository,
        CategoriesRepository $categoriesRepository
    ) {
        $this->productsRepository = $productsRepository;
        $this->categoriesRepository = $categoriesRepository;
    }

    /**
     * Hiển thị danh sách sản phẩm
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = $this->productsRepository->paginate($request);
        $categories = $this->categoriesRepository->all();

        return view('source.admin.product.index', compact('products', 'categories'));
    }

    /**
     * Hiển thị form tạo sản phẩm mới
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = $this->categoriesRepository->all();
        $parentProducts = $this->productsRepository->getPossibleParents();

        return view('source.admin.product.create', compact('categories', 'parentProducts'));
    }

    /**
     * Lưu sản phẩm mới vào database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        // Xử lý slug nếu trống
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Xử lý checkbox
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $data['is_recurring'] = $request->has('is_recurring') ? 1 : 0;
        $data['auto_renew'] = $request->has('auto_renew') ? 1 : 0;

        // Xử lý upload hình ảnh
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        // Bỏ phần kiểm tra định dạng JSON cho meta_data và options

        try {
            $product = $this->productsRepository->validateAndCreate($data);
            return redirect()->route('admin.products.index')
                ->with('success', 'Sản phẩm đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị thông tin chi tiết sản phẩm
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = $this->productsRepository->find($id);
        if (!$product) {
            return redirect()->route('admin.products.index')
                ->withErrors(['error' => 'Sản phẩm không tồn tại!']);
        }

        return view('source.admin.product.show', compact('product'));
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = $this->productsRepository->find($id);
        if (!$product) {
            return redirect()->route('admin.products.index')
                ->withErrors(['error' => 'Sản phẩm không tồn tại!']);
        }

        $categories = $this->categoriesRepository->all();
        $parentProducts = $this->productsRepository->getPossibleParentsExcept($id);

        return view('source.admin.product.edit', compact('product', 'categories', 'parentProducts'));
    }

    /**
     * Cập nhật sản phẩm
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = $this->productsRepository->find($id);
        if (!$product) {
            return redirect()->route('admin.products.index')
                ->withErrors(['error' => 'Sản phẩm không tồn tại!']);
        }

        $data = $request->all();

        // Xử lý slug nếu trống
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Xử lý checkbox
        $data['is_featured'] = $request->has('is_featured') ? 1 : 0;
        $data['is_recurring'] = $request->has('is_recurring') ? 1 : 0;
        $data['auto_renew'] = $request->has('auto_renew') ? 1 : 0;

        // Xử lý upload hình ảnh
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Xóa hình ảnh cũ
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Upload hình ảnh mới
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        // Bỏ phần kiểm tra định dạng JSON cho meta_data và options

        try {
            $this->productsRepository->validateAndUpdate($data, $id);
            return redirect()->route('admin.products.index')
                ->with('success', 'Sản phẩm đã được cập nhật thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
    /**
     * Xóa sản phẩm
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $product = $this->productsRepository->find($id);
            if (!$product) {
                return redirect()->route('admin.products.index')
                    ->withErrors(['error' => 'Sản phẩm không tồn tại!']);
            }

            // Kiểm tra xem sản phẩm có biến thể không
            if ($product->variants && $product->variants->count() > 0) {
                return redirect()->back()
                    ->withErrors(['error' => 'Không thể xóa sản phẩm này vì có ' . $product->variants->count() . ' biến thể!']);
            }

            // Kiểm tra xem sản phẩm đã được mua chưa - sử dụng raw query để tránh lỗi
            $orderItemsCount = DB::table('order_items')
                ->where('product_id', $id) // Thay 'product_id' bằng tên cột thực tế
                ->count();

            if ($orderItemsCount > 0) {
                return redirect()->back()
                    ->withErrors(['error' => 'Không thể xóa sản phẩm này vì đã có ' . $orderItemsCount . ' đơn hàng liên quan!']);
            }

            // Xóa hình ảnh nếu có
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $this->productsRepository->delete($id);

            return redirect()->route('admin.products.index')
                ->with('success', 'Sản phẩm đã được xóa thành công!');
        } catch (\Exception $e) {
            Log::error('Product deletion error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Lỗi xóa sản phẩm: ' . $e->getMessage()]);
        }
    }

    /**
     * Chuyển đổi trạng thái sản phẩm
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus($id)
    {
        try {
            $this->productsRepository->toggleStatus($id);
            return redirect()->back()
                ->with('success', 'Trạng thái sản phẩm đã được thay đổi!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}
