<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;
use App\Models\Customers;
use App\Mail\DepositRequest;
use App\Mail\DepositRequestAdmin;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Deposit;
use App\Models\deposits;

class WalletController extends Controller
{
    /**
     * Hiển thị trang nạp tiền
     */
    public function deposit()
    {
        // Lấy thông tin cấu hình
        $config = Config::current();

        // Lấy thông tin khách hàng
        $user = Auth::user();
        $customer = $user->customer;

        // Kiểm tra và tạo customer nếu chưa có
        if (!$customer) {
            $customer = new Customers();
            $customer->user_id = $user->id;
            $customer->company_name = $user->name; // Mặc định lấy tên từ user
            $customer->status = 'active';
            $customer->source = 'website';
            $customer->balance = 0; // Số dư ban đầu
            $customer->save();

            // Refresh user để lấy thông tin customer mới tạo
            $user->refresh();
            $customer = $user->customer;
        }

        // Mốc nạp tiền
        $depositAmounts = [
            5 => 5000000,  // 5 triệu
            10 => 10000000, // 10 triệu
            15 => 15000000, // 15 triệu
        ];

        // Min/Max deposit từ cấu hình
        $minDeposit = $config->min_deposit_amount ?? 100000;
        $maxDeposit = $config->max_deposit_amount ?? 100000000;

        return view('source.web.wallet.deposit', compact(
            'config',
            'customer',
            'depositAmounts',
            'minDeposit',
            'maxDeposit'
        ));
    }

    /**
     * Xử lý yêu cầu nạp tiền
     */
    public function processDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100000',
            'payment_method' => 'required|in:bank,momo,zalopay',
            'agree_terms' => 'required|accepted'
        ]);

        // Lấy thông tin cấu hình
        $config = Config::current();

        // Kiểm tra giới hạn nạp tiền
        $minDeposit = $config->min_deposit_amount ?? 100000;
        $maxDeposit = $config->max_deposit_amount ?? 100000000;

        if ($request->amount < $minDeposit || $request->amount > $maxDeposit) {
            return back()->withErrors([
                'amount' => "Số tiền nạp phải từ " . number_format($minDeposit) . " đ đến " . number_format($maxDeposit) . " đ"
            ])->withInput();
        }

        // Lấy thông tin khách hàng
        $user = Auth::user();
        if (!$user || !$user->email) {
            return back()->withErrors(['email' => 'Không thể xác định email người dùng'])->withInput();
        }

        $customer = $user->customer;
        if (!$customer) {
            return back()->withErrors(['customer' => 'Không tìm thấy thông tin khách hàng'])->withInput();
        }

        // Tạo mã giao dịch
        $transactionCode = 'DEP' . time() . Str::random(5);

        // Tính toán số tiền nạp và tiền thưởng (nếu có)
        $originalAmount = $request->amount;
        $finalAmount = $originalAmount;
        $bonusAmount = 0;

        // Kiểm tra điều kiện thưởng: nạp từ 10 triệu trở lên được thưởng 5%
        if ($originalAmount >= 10000000) {
            $bonusAmount = round($originalAmount * 5 / 100);
            $finalAmount = $originalAmount + $bonusAmount;

            // Log để theo dõi
            Log::info("Tiền thưởng nạp tiền: Gốc: {$originalAmount}, Thưởng 5%: {$bonusAmount}, Tổng: {$finalAmount}");
        }

        // Chuẩn bị dữ liệu email
        $depositData = [
            'transaction_code' => $transactionCode,
            'amount' => $originalAmount,
            'payment_method' => $request->payment_method,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_id' => $customer->id,
            'date' => now()->format('d/m/Y H:i:s'),
            'note_format' => $config->deposit_note_format ? str_replace('{customer_id}', $customer->id, $config->deposit_note_format) : "NapTien{$customer->id}",
        ];

        // Thêm thông tin thưởng nếu có
        if ($bonusAmount > 0) {
            $depositData['bonus_amount'] = $bonusAmount;
            $depositData['bonus_percent'] = 5;
            $depositData['final_amount'] = $finalAmount;
        }

        // Thêm thông tin chi tiết dựa vào phương thức thanh toán
        $paymentDetails = [];

        switch ($request->payment_method) {
            case 'bank':
                $depositData['bank_info'] = [
                    'bank_name' => $config->company_bank_name,
                    'account_number' => $config->company_bank_account_number,
                    'account_name' => $config->company_bank_account_name,
                    'branch' => $config->company_bank_branch,
                ];

                $paymentDetails = [
                    'bank_name' => $config->company_bank_name,
                    'account_number' => $config->company_bank_account_number,
                    'account_name' => $config->company_bank_account_name,
                    'branch' => $config->company_bank_branch,
                ];

                if ($config->company_bank_qr_code) {
                    $depositData['qr_code_url'] = url('storage/' . $config->company_bank_qr_code);
                }
                break;

            case 'momo':
                $depositData['momo_info'] = [
                    'phone' => $config->momo_phone_number,
                    'account_name' => $config->momo_account_name,
                ];

                $paymentDetails = [
                    'phone' => $config->momo_phone_number,
                    'account_name' => $config->momo_account_name,
                ];

                if ($config->momo_qr_code) {
                    $depositData['qr_code_url'] = url('storage/' . $config->momo_qr_code);
                }
                break;

            case 'zalopay':
                $depositData['zalopay_info'] = [
                    'phone' => $config->zalopay_phone_number,
                    'account_name' => $config->zalopay_account_name,
                ];

                $paymentDetails = [
                    'phone' => $config->zalopay_phone_number,
                    'account_name' => $config->zalopay_account_name,
                ];

                if ($config->zalopay_qr_code) {
                    $depositData['qr_code_url'] = url('storage/' . $config->zalopay_qr_code);
                }
                break;
        }

        // Thêm thông tin về tiền thưởng vào payment_details
        if ($bonusAmount > 0) {
            $paymentDetails['original_amount'] = $originalAmount;
            $paymentDetails['bonus_amount'] = $bonusAmount;
            $paymentDetails['bonus_percent'] = 5;
        }

        // Lưu yêu cầu nạp tiền vào database
        $deposit = deposits::create([
            'transaction_code' => $transactionCode,
            'customer_id' => $customer->id,
            'amount' => $finalAmount, // Lưu tổng số tiền đã cộng thưởng
            'payment_method' => $request->payment_method,
            'note' => $depositData['note_format'],
            'status' => 'pending',
            'payment_details' => $paymentDetails,
        ]);

        // Kiểm tra email người dùng có hợp lệ không
        if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            // Gửi email cho khách hàng
            try {
                Mail::to($user->email)->send(new DepositRequest($depositData));
            } catch (\Exception $e) {
                // Log lỗi để debug
                Log::error('Lỗi gửi email cho khách hàng: ' . $e->getMessage());
            }
        } else {
            Log::warning('Email khách hàng không hợp lệ: ' . $user->email);
        }

        // Kiểm tra email admin có hợp lệ không
        $adminEmail = config('mail.admin_email');
        if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            // Gửi email cho admin
            try {
                Mail::to($adminEmail)->send(new DepositRequestAdmin($depositData));
            } catch (\Exception $e) {
                // Log lỗi để debug
                Log::error('Lỗi gửi email cho admin: ' . $e->getMessage());
            }
        } else {
            Log::warning('Email admin không hợp lệ hoặc không được cấu hình: ' . $adminEmail);
        }

        // Chuyển hướng với thông báo thành công
        return redirect()->route('deposit.success', ['code' => $transactionCode])
            ->with('deposit_data', $depositData);
    }

    /**
     * Hiển thị trang thành công
     */
    public function depositSuccess(Request $request)
    {
        // Kiểm tra xem có dữ liệu session không
        if (!session()->has('deposit_data')) {
            return redirect()->route('deposit');
        }

        $depositData = session('deposit_data');
        $code = $request->code;

        // Kiểm tra mã giao dịch
        if ($depositData['transaction_code'] !== $code) {
            return redirect()->route('deposit');
        }

        return view('source.web.wallet.deposit_success', compact('depositData'));
    }
}
