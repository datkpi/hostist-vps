<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FrontendAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            // Chỉ lưu URL hiện tại nếu nó là GET method
            if ($request->isMethod('get')) {
                session()->put('url.intended', url()->current());
            } else {
                // Nếu là POST, lưu URL trang sản phẩm hoặc trang chủ thay thế
                // Bạn có thể điều chỉnh route này tùy theo ứng dụng của bạn
                session()->put('url.intended', route('homepage'));

                // Hoặc lưu tham số referer nếu có
                if ($request->headers->has('referer')) {
                    session()->put('url.intended', $request->headers->get('referer'));
                }
            }

            return redirect()->route('login')->with('message', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = Auth::user();

        // Nếu đang truy cập route checkout, kiểm tra thông tin khách hàng
        if ($request->routeIs('checkout.*')) {
            // Kiểm tra thông tin cần thiết cho checkout
            if (empty($user->phone) || empty($user->address)) {
                session()->put('url.intended', url()->current());
                return redirect()->route('customer.profile')->with('message', 'Vui lòng cập nhật thông tin liên hệ để tiếp tục mua hàng.');
            }

            // Kiểm tra customer nếu cần
            $customer = $user->customer;
            if (!$customer) {
                session()->put('url.intended', url()->current());
                return redirect()->route('customer.profile')->with('message', 'Vui lòng cập nhật thông tin khách hàng để tiếp tục mua hàng.');
            }
        }

        return $next($request);
    }
}
