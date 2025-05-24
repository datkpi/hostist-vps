<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cashbacks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashbackAdminController extends Controller
{
    /**
     * Hiển thị danh sách các yêu cầu hoàn tiền
     */
    public function index(Request $request)
    {
        $query = Cashbacks::with(['customer', 'order']);

        // Lọc theo trạng thái nếu có
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Sắp xếp mặc định theo thời gian tạo, mới nhất lên đầu
        $cashbacks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('source.admin.cashback.index', compact('cashbacks'));
    }

    /**
     * Phê duyệt yêu cầu hoàn tiền
     */
    public function approve($id)
    {
        $cashback = Cashbacks::findOrFail($id);

        // Chỉ có thể duyệt yêu cầu đang ở trạng thái chờ duyệt
        if ($cashback->status != 'pending') {
            return redirect()->back()->with('error', 'Chỉ có thể duyệt yêu cầu đang chờ xử lý.');
        }

        // Cập nhật trạng thái
        $cashback->status = 'approved';
        $cashback->approved_by = Auth::id();
        $cashback->approved_at = now();
        $cashback->save();

        return redirect()->back()->with('success', 'Yêu cầu hoàn tiền đã được duyệt.');
    }

    /**
     * Từ chối yêu cầu hoàn tiền
     */
    public function reject(Request $request, $id)
    {
        $cashback = Cashbacks::findOrFail($id);

        // Chỉ có thể từ chối yêu cầu đang ở trạng thái chờ duyệt
        if ($cashback->status != 'pending') {
            return redirect()->back()->with('error', 'Chỉ có thể từ chối yêu cầu đang chờ xử lý.');
        }

        // Cập nhật trạng thái
        $cashback->status = 'rejected';
        $cashback->approved_by = Auth::id();
        $cashback->approved_at = now();
        $cashback->admin_note = $request->admin_note ?? 'Yêu cầu hoàn tiền không hợp lệ';
        $cashback->save();

        return redirect()->back()->with('success', 'Yêu cầu hoàn tiền đã bị từ chối.');
    }

    /**
     * Đánh dấu đã xử lý (đã chuyển khoản)
     */
    public function markProcessed($id)
    {
        $cashback = Cashbacks::findOrFail($id);

        // Chỉ có thể đánh dấu đã xử lý với yêu cầu đã được duyệt
        if ($cashback->status != 'approved') {
            return redirect()->back()->with('error', 'Chỉ có thể đánh dấu đã xử lý với yêu cầu đã được duyệt.');
        }

        // Cập nhật trạng thái
        $cashback->status = 'processed';
        $cashback->processed_by = Auth::id();
        $cashback->processed_at = now();
        $cashback->save();

        return redirect()->back()->with('success', 'Yêu cầu hoàn tiền đã được đánh dấu là đã xử lý.');
    }
}
