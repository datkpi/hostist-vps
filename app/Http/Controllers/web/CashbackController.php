<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cashbacks;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CashbackController extends Controller
{
    /**
     * Đăng ký nhận hoàn tiền
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Kiểm tra đơn hàng thuộc về người dùng hiện tại
        $order = Orders::findOrFail($request->order_id);
        if ($order->customer_id != Auth::user()->customer->id) {
            return redirect()->back()->with('error', 'Bạn không có quyền thực hiện thao tác này.');
        }

        // Kiểm tra đơn hàng đủ điều kiện
        if ($order->status != 'completed' || $order->total_amount < 9000000) {
            return redirect()->back()->with('error', 'Đơn hàng không đủ điều kiện nhận hoàn tiền.');
        }

        // Kiểm tra đã có yêu cầu hoàn tiền cho đơn hàng này chưa
        $existingCashback = Cashbacks::where('order_id', $order->id)->first();
        if ($existingCashback) {
            return redirect()->back()->with('error', 'Đơn hàng này đã có yêu cầu hoàn tiền.');
        }

        // Tính số tiền hoàn lại (12% tổng đơn hàng)
        $cashbackAmount = $order->total_amount * 0.12;

        // Tạo yêu cầu hoàn tiền mới
        Cashbacks::create([
            'order_id' => $order->id,
            'customer_id' => Auth::user()->customer->id,
            'amount' => $cashbackAmount,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_holder' => $request->account_holder,
            'branch' => $request->branch,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Yêu cầu hoàn tiền đã được gửi thành công. Chúng tôi sẽ xử lý trong thời gian sớm nhất.');
    }

    /**
     * Hiển thị trạng thái hoàn tiền
     */
    public function getStatus(Request $request)
    {
        $cashback = Cashbacks::with(['order'])->findOrFail($request->id);

        // Kiểm tra quyền truy cập
        if ($cashback->customer_id != Auth::user()->customer->id) {
            return '<div class="alert alert-danger">Bạn không có quyền xem thông tin này.</div>';
        }

        // Format dữ liệu
        $statusText = '';
        switch ($cashback->status) {
            case 'pending':
                $statusText = '<span class="badge bg-warning text-dark">Đang chờ duyệt</span>';
                break;
            case 'approved':
                $statusText = '<span class="badge bg-primary">Đã duyệt - Đang chờ xử lý</span>';
                break;
            case 'processed':
                $statusText = '<span class="badge bg-success">Đã chuyển khoản</span>';
                break;
            case 'rejected':
                $statusText = '<span class="badge bg-danger">Đã từ chối</span>';
                break;
            default:
                $statusText = '<span class="badge bg-secondary">' . ucfirst($cashback->status) . '</span>';
        }

        // Lịch sử xử lý
        $history = '';
        $history .= '<li>' . $cashback->created_at->format('d/m/Y') . ': Gửi yêu cầu hoàn tiền</li>';

        if ($cashback->approved_at) {
            $history .= '<li>' . $cashback->approved_at->format('d/m/Y') . ': Yêu cầu được duyệt</li>';
        }

        if ($cashback->processed_at) {
            $history .= '<li>' . $cashback->processed_at->format('d/m/Y') . ': Đã chuyển khoản</li>';
        }

        $html = '
        <div>
            <div class="mb-3">
                <p><strong>Đơn hàng:</strong> #' . $cashback->order->order_number . '</p>
                <p><strong>Tổng đơn hàng:</strong> ' . number_format($cashback->order->total_amount, 0, ',', '.') . ' đ</p>
                <p><strong>Số tiền hoàn (12%):</strong> ' . number_format($cashback->amount, 0, ',', '.') . ' đ</p>
            </div>

            <div class="mb-3">
                <h6>Thông tin tài khoản:</h6>
                <ul class="list-unstyled">
                    <li><strong>Ngân hàng:</strong> ' . $cashback->bank_name . '</li>
                    <li><strong>Số tài khoản:</strong> ' . substr($cashback->account_number, 0, -4) . '****</li>
                    <li><strong>Chủ tài khoản:</strong> ' . $cashback->account_holder . '</li>
                    ' . ($cashback->branch ? '<li><strong>Chi nhánh:</strong> ' . $cashback->branch . '</li>' : '') . '
                </ul>
            </div>

            <div class="mb-3">
                <h6>Trạng thái hiện tại:</h6>
                <p>' . $statusText . '</p>
            </div>

            <div class="mb-3">
                <h6>Lịch sử xử lý:</h6>
                <ul>
                    ' . $history . '
                </ul>
            </div>

            ' . ($cashback->admin_note ? '
            <div class="mb-3">
                <h6>Ghi chú:</h6>
                <p>' . $cashback->admin_note . '</p>
            </div>' : '') . '
        </div>';

        return $html;
    }
}
