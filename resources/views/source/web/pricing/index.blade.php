@extends('layouts.web.default')

@section('content')
    <!-- new pricing section -->
    <section class="hosting-plans-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="hosting-plans-title">
                    Our Hosting Solutions
                </h2>
                <p class="hosting-plans-subtitle text-muted">Choose the perfect plan for your needs</p>
            </div>
            
            <div class="row g-4">
                @forelse($categories->pluck('products')->flatten() as $product)
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="hosting-plan-card h-100">
                            <div class="hosting-plan-header text-center">
                                <h5 class="hosting-plan-name mb-3">{{ $product->name }}</h5>
                                @if($product->short_description)
                                    <p class="hosting-plan-description mb-3">{{ $product->short_description }}</p>
                                @endif
                                
                                {{-- Hiển thị pricing theo chu kỳ --}}
                                <div class="hosting-pricing-options">
                                    @if($product->is_recurring)
                                        @php
                                            $basePrice = $product->sale_price ?: $product->price;
                                        @endphp
                                        {{-- Hiển thị các gói theo năm --}}
                                        <div class="pricing-option">
                                            <span class="pricing-period">01 năm x</span>
                                            <span class="pricing-amount">{{ number_format($basePrice, 0, ',', '.') }} đ</span>
                                            <span class="pricing-total">= {{ number_format($basePrice * 1, 0, ',', '.') }} đ</span>
                                        </div>
                                        <div class="pricing-option">
                                            <span class="pricing-period">02 năm x</span>
                                            <span class="pricing-amount">{{ number_format($basePrice, 0, ',', '.') }} đ</span>
                                            <span class="pricing-total">= {{ number_format($basePrice * 2, 0, ',', '.') }} đ</span>
                                        </div>
                                        <div class="pricing-option">
                                            <span class="pricing-period">03 năm x</span>
                                            <span class="pricing-amount">{{ number_format($basePrice, 0, ',', '.') }} đ</span>
                                            <span class="pricing-total">= {{ number_format($basePrice * 3, 0, ',', '.') }} đ</span>
                                        </div>
                                        <div class="pricing-option selected">
                                            <i class="fas fa-check pricing-check-icon"></i>
                                            <span class="pricing-period">05 năm x</span>
                                            <span class="pricing-amount">{{ number_format($basePrice, 0, ',', '.') }} đ</span>
                                            <span class="pricing-total">= {{ number_format($basePrice * 5, 0, ',', '.') }} đ</span>
                                        </div>
                                    @else
                                        {{-- Hiển thị giá đơn giản --}}
                                        <div class="pricing-option selected">
                                            @if($product->sale_price && $product->sale_price < $product->price)
                                                <span class="pricing-old-price">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                                                <span class="pricing-amount">{{ number_format($product->sale_price, 0, ',', '.') }} đ</span>
                                            @else
                                                <span class="pricing-amount">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                            @endif
                                            @if($product->is_recurring)
                                                @if($product->recurring_period == 1)
                                                    <span class="pricing-period">/tháng</span>
                                                @else
                                                    <span class="pricing-period">/{{ $product->recurring_period }} tháng</span>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="hosting-plan-features">
                                @if($product->description)
                                    {!! $product->description !!}
                                @else
                                    <ul class="hosting-feature-list">
                                        <li class="hosting-feature-item">
                                            <i class="fas fa-check hosting-feature-icon"></i>
                                            High Quality Service
                                        </li>
                                        <li class="hosting-feature-item">
                                            <i class="fas fa-check hosting-feature-icon"></i>
                                            24/7 Professional Support
                                        </li>
                                        <li class="hosting-feature-item">
                                            <i class="fas fa-check hosting-feature-icon"></i>
                                            Easy Installation
                                        </li>
                                        <li class="hosting-feature-item">
                                            <i class="fas fa-check hosting-feature-icon"></i>
                                            Secure & Reliable
                                        </li>
                                    </ul>
                                @endif
                            </div>
                            
                            <div class="hosting-plan-footer mt-auto">
                                @if($product->stock !== -1 && $product->stock <= 5 && $product->stock > 0)
                                    <div class="hosting-stock-warning mb-3">
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Chỉ còn {{ $product->stock }} sản phẩm
                                        </small>
                                    </div>
                                @endif
                                
                                <div class="hosting-plan-actions d-grid">
                                    <a href="{{ route('service.detail', $product->slug) ?? '#' }}" 
                                       class="btn btn-primary hosting-register-btn">
                                        <i class="fas fa-rocket me-2"></i>
                                        Đăng ký ngay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Fallback với data mẫu nếu không có sản phẩm --}}
                    <div class="col-lg-4 col-md-6">
                        <div class="hosting-plan-card h-100">
                            <div class="hosting-plan-header text-center">
                                <div class="hosting-plan-price">
                                    <span class="hosting-plan-current-price">1.800.000</span>
                                    <span class="hosting-plan-period">đ/năm</span>
                                </div>
                                <h5 class="hosting-plan-name mt-3">SSL Certificates</h5>
                            </div>
                            <div class="hosting-plan-features">
                                <ul class="hosting-feature-list">
                                    <li class="hosting-feature-item">
                                        <i class="fas fa-check hosting-feature-icon"></i>
                                        Website Identity Verification
                                    </li>
                                    <li class="hosting-feature-item">
                                        <i class="fas fa-check hosting-feature-icon"></i>
                                        Encrypted Data Transmission
                                    </li>
                                    <li class="hosting-feature-item">
                                        <i class="fas fa-check hosting-feature-icon"></i>
                                        HTTPS Website Security
                                    </li>
                                    <li class="hosting-feature-item">
                                        <i class="fas fa-check hosting-feature-icon"></i>
                                        Improved Search Rankings
                                    </li>
                                </ul>
                            </div>
                            <div class="hosting-plan-footer mt-auto">
                                <div class="hosting-plan-actions d-grid">
                                    <a href="#" class="btn btn-outline-primary hosting-detail-btn">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Xem Chi Tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            
            {{-- Thêm section thống kê nếu cần --}}
            @if(isset($stats))
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="hosting-stats-section">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="hosting-stat-item">
                                        <h3 class="hosting-stat-number">{{ $stats['total_products'] ?? 0 }}</h3>
                                        <p class="hosting-stat-label">Sản Phẩm</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="hosting-stat-item">
                                        <h3 class="hosting-stat-number">{{ $stats['total_categories'] ?? 0 }}</h3>
                                        <p class="hosting-stat-label">Danh Mục</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="hosting-stat-item">
                                        <h3 class="hosting-stat-number">24/7</h3>
                                        <p class="hosting-stat-label">Hỗ Trợ</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <!-- end new pricing section -->
@endsection

@push('header_css')
<style>
/* Custom CSS for Hosting Plans - Tránh conflict với CSS cũ */
.hosting-plans-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.hosting-plans-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.hosting-plans-subtitle {
    font-size: 1.2rem;
    color: #6c757d;
}

.hosting-plan-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.hosting-plan-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #007bff;
}

.hosting-plan-header {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
}

.hosting-plan-name {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.hosting-plan-description {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 0;
}

/* Pricing Options Styles */
.hosting-pricing-options {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-top: 1rem;
}

.pricing-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    margin: 0.5rem 0;
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    flex-wrap: wrap;
}

.pricing-option:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.1);
}

.pricing-option.selected {
    border-color: #28a745;
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.2);
}

.pricing-check-icon {
    position: absolute;
    left: 0.5rem;
    color: #28a745;
    font-size: 0.9rem;
}

.pricing-option.selected .pricing-period,
.pricing-option.selected .pricing-amount {
    margin-left: 1.5rem;
}

.pricing-period {
    font-weight: 500;
    color: #495057;
    font-size: 0.95rem;
    flex: 0 0 auto;
}

.pricing-amount {
    font-weight: 700;
    color: #2c3e50;
    font-size: 1rem;
    flex: 0 0 auto;
}

.pricing-total {
    font-weight: 600;
    color: #007bff;
    font-size: 1rem;
    flex: 0 0 auto;
}

.pricing-old-price {
    color: #6c757d;
    text-decoration: line-through;
    font-size: 0.9rem;
    margin-right: 0.5rem;
}

.hosting-plan-features {
    flex-grow: 1;
    margin-bottom: 1.5rem;
}

.hosting-feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hosting-feature-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    color: #495057;
}

.hosting-feature-item:last-child {
    border-bottom: none;
}

.hosting-feature-icon {
    color: #28a745;
    margin-right: 0.75rem;
    font-size: 0.9rem;
    width: 16px;
    flex-shrink: 0;
}

.hosting-plan-footer {
    margin-top: auto;
}

.hosting-stock-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    text-align: center;
}

.hosting-register-btn {
    border-radius: 8px;
    padding: 1rem 2rem;
    font-weight: 600;
    font-size: 1.1rem;
    text-decoration: none;
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
    transition: all 0.3s ease;
}

.hosting-register-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
}

.hosting-stats-section {
    background: #ffffff;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.hosting-stat-item {
    padding: 1rem;
}

.hosting-stat-number {
    font-size: 3rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 0.5rem;
}

.hosting-stat-label {
    font-size: 1.1rem;
    color: #6c757d;
    margin: 0;
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hosting-plans-title {
        font-size: 2rem;
    }
    
    .hosting-plan-card {
        padding: 1.5rem;
    }
    
    .pricing-option {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .hosting-stat-number {
        font-size: 2.5rem;
    }
}

/* Override để description HTML render đúng */
.hosting-plan-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hosting-plan-features li {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f8f9fa;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    color: #495057;
}

.hosting-plan-features li:last-child {
    border-bottom: none;
}

.hosting-plan-features li:before {
    content: "✓";
    color: #28a745;
    margin-right: 0.75rem;
    font-weight: bold;
}
</style>
@endpush

@push('footer_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pricing option selection
    const pricingOptions = document.querySelectorAll('.pricing-option');
    
    pricingOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from siblings
            const siblings = this.parentElement.querySelectorAll('.pricing-option');
            siblings.forEach(sibling => {
                sibling.classList.remove('selected');
                const checkIcon = sibling.querySelector('.pricing-check-icon');
                if (checkIcon) {
                    checkIcon.remove();
                }
            });
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Add check icon if not exists
            if (!this.querySelector('.pricing-check-icon')) {
                const checkIcon = document.createElement('i');
                checkIcon.className = 'fas fa-check pricing-check-icon';
                this.insertBefore(checkIcon, this.firstChild);
                
                // Adjust margins for content
                const period = this.querySelector('.pricing-period');
                const amount = this.querySelector('.pricing-amount');
                if (period) period.style.marginLeft = '1.5rem';
                if (amount) amount.style.marginLeft = '1.5rem';
            }
        });
    });
    
    // Card hover effects
    const cards = document.querySelectorAll('.hosting-plan-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Register button effects
    const registerBtns = document.querySelectorAll('.hosting-register-btn');
    registerBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
@endpush