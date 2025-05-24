  <!-- jQery -->
  <script src="{{asset('assets/web/hostit/js/jquery-3.4.1.min.js')}}"></script>
  <!-- bootstrap js -->
  <script src="{{asset('assets/web/hostit/js/bootstrap.js')}}"></script>
  <!-- custom js -->
  <script src="{{asset('assets/web/hostit/js/custom.js')}}"></script>
  <script>
// Tự động ẩn thông báo sau 5 giây
document.addEventListener('DOMContentLoaded', function() {
    // Chọn tất cả các alert
    var alerts = document.querySelectorAll('.alert');

    // Đặt timeout để ẩn sau 5 giây
    alerts.forEach(function(alert) {
        setTimeout(function() {
            // Sử dụng Bootstrap để ẩn alert
            $(alert).alert('close');
        }, 5000);
    });
});
</script>
<!-- Thêm SweetAlert2 vào layout chính của bạn -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra nếu có thông báo thành công từ session
    @if(session('success'))
        Swal.fire({
            title: 'Thành công!',
            text: "{{ session('success') }}",
            icon: 'success',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    @endif

    // Hiển thị lỗi nếu có
    @if(session('error'))
        Swal.fire({
            title: 'Lưu ý!',
            text: "{{ session('error') }}",
            icon: 'warning',
            confirmButtonText: 'Đã hiểu'
        });
    @endif
});
</script>
