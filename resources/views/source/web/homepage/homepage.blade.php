@extends('layouts.web.index')

@section('content')
    <section class="service_section layout_padding">
        <div class="container">
            <div class="heading_container heading_center">
                <h2>
                    Our Services
                </h2>
            </div>
        </div>
        <div class="container">
            <div class="row">
                @forelse($services as $service)
                    <div class="col-md-6 col-lg-4">
                        <div class="box">
                            <div class="img-box">

                            </div>
                            <div class="detail-box">
                                <h4>
                                    {{ $service->name }}
                                </h4>
                                <p>
                                    @if ($service->short_description)
                                        {{ $service->short_description }}
                                    @else
                                        {!! Str::limit(strip_tags($service->description), 150) !!}
                                    @endif
                                </p>
                                <a href="{{ route('service.detail', $service->slug) }}">
                                    Read More
                                    <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Hiển thị dữ liệu mẫu nếu không có dịch vụ nào -->
                    <div class="col-md-6 col-lg-4">
                        <div class="box">
                            <div class="img-box">

                            </div>
                            <div class="detail-box">
                                <h4>
                                    SSL Certificates
                                </h4>
                                <p>
                                    SSL certificates are digital credentials that authenticate website identity and enable
                                    encrypted connections between web servers and browsers
                                </p>
                                <a href="#">
                                    Read More
                                    <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- Thêm các dịch vụ mẫu khác từ template gốc -->
                @endforelse
            </div>
        </div>
    </section>
    <!-- end service section -->

    <!-- about section -->

    <section class="about_section layout_padding-bottom">
        <div class="container  ">
            <div class="row">
                <div class="col-md-6">
                    <div class="detail-box">
                        <div class="heading_container">
                            <h2>
                                About Our Hosting Company
                            </h2>
                        </div>
                        <p>
                            Founded with a passion for web technology and customer service, our hosting company has grown to
                            become a
                            trusted provider of digital infrastructure solutions. We pride ourselves on delivering reliable,
                            high-performance hosting services that empower businesses of all sizes to establish and expand
                            their
                            online presence.<br>
                            Our team consists of experienced IT professionals dedicated to ensuring your websites and
                            applications run
                            smoothly 24/7. We've invested in state-of-the-art data centers, cutting-edge technologies, and
                            robust
                            security systems to provide you with hosting solutions that meet the highest industry standards.
                            What sets us apart is our commitment to personalized support. We understand that every client
                            has unique
                            needs, which is why we offer customized hosting packages ranging from shared hosting for
                            startups to
                            dedicated servers for enterprise-level operations.<br>
                            As we continue to evolve with the digital landscape, we remain focused on our core mission:
                            providing you
                            with the technological foundation you need to succeed online while delivering exceptional value
                            and
                            service. </p>
                        <a href="">
                            Read More
                        </a>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="img-box">
                        <img src="images/about-img.png" alt="">
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- end about section -->


    <!-- server section -->

    <section class="server_section">
        <div class="container ">
            <div class="row">
                <div class="col-md-6">
                    <div class="img-box">
                        <img src="{{ asset('assets/web/hostit/images/server-img.jpg') }}" alt="">
                        <div class="play_btn">
                            <button>
                                <i class="fa fa-play" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-box">
                        <div class="heading_container">
                            <h2>
                                Website Services Overview
                            </h2>
                            <p>
                                Our website offers comprehensive hosting solutions to power your online presence. From
                                shared hosting
                                for small websites to powerful dedicated servers for business applications, we provide a
                                range of
                                options tailored to your needs. Our services include shared hosting, dedicated hosting,
                                cloud hosting,
                                VPS solutions, WordPress-optimized environments, and domain registration services. Each
                                package comes
                                with reliable support, security features, and the performance you need to succeed online.
                                Let us handle
                                the technical details while you focus on growing your business.
                            </p>
                        </div>
                        <a href="">
                            Read More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- end server section -->

    <!-- price section -->

    <section class="price_section layout_padding">
        <div class="container">
            <div class="heading_container heading_center">
                <h2>
                    Our Hosting Solutions
                </h2>
            </div>
            <div class="price_container">
                @forelse($hostingSolutions as $solution)
                    <div class="box">
                        <div class="detail-box">
                            <!-- Hiển thị giá và kỳ hạn -->
                            <div class="price-badge">
                                @if ($solution->is_recurring && $solution->recurring_period)
                                    @if ($solution->sale_price)
                                        <h2>{{ number_format($solution->sale_price, 0, ',', '.') }}
                                            <span>Đ</span>
                                        </h2>
                                        <div class="period-tag">
                                            {{ $solution->recurring_period == 12 ? 'Năm' : ($solution->recurring_period == 1 ? 'Tháng' : $solution->recurring_period . ' Tháng') }}
                                        </div>
                                        @if ($solution->price > $solution->sale_price)
                                            <div class="original-price">
                                                <del>{{ number_format($solution->price, 0, ',', '.') }}Đ</del>
                                            </div>
                                        @endif
                                    @else
                                        <h2>{{ number_format($solution->price, 0, ',', '.') }}
                                            <span>Đ</span>
                                        </h2>
                                        <div class="period-tag">
                                            {{ $solution->recurring_period == 12 ? 'Năm' : ($solution->recurring_period == 1 ? 'Tháng' : $solution->recurring_period . ' Tháng') }}
                                        </div>
                                    @endif
                                @else
                                    @if ($solution->sale_price)
                                        <h2>{{ number_format($solution->sale_price, 0, ',', '.') }} <span>Đ</span></h2>
                                        @if ($solution->price > $solution->sale_price)
                                            <div class="original-price">
                                                <del>{{ number_format($solution->price, 0, ',', '.') }}Đ</del>
                                            </div>
                                        @endif
                                    @else
                                        <h2>{{ number_format($solution->price, 0, ',', '.') }} <span>Đ</span></h2>
                                    @endif
                                @endif
                            </div>

                            <!-- Tên gói dịch vụ -->
                            <h6 class="package-title">
                                {{ $solution->categoryObject->name ?? $solution->name }}
                            </h6>

                            @php
                                // Lấy các features từ description hoặc meta_data
                                $features = [];

                                // Kiểm tra nếu có meta_data và có features trong đó
                                if ($solution->meta_data) {
                                    // meta_data đã là array do accessor chuyển đổi
                                    if (
                                        isset($solution->meta_data['features']) &&
                                        is_array($solution->meta_data['features'])
                                    ) {
                                        $features = $solution->meta_data['features'];
                                    }
                                }

                                // Nếu không có features, tìm trong description
                                if (empty($features) && $solution->description) {
                                    // Tìm các dòng bắt đầu bằng dấu "-" hoặc "*" trong description
                                    preg_match_all(
                                        '/[\-\*]\s*([^\n\r]+)/',
                                        strip_tags($solution->description),
                                        $matches,
                                    );
                                    if (!empty($matches[1])) {
                                        $features = array_slice($matches[1], 0, 6); // Lấy tối đa 6 features
                                    }
                                }

                                // Nếu vẫn không có, tạo features mẫu dựa vào nội dung mô tả ngắn
                                if (empty($features) && $solution->short_description) {
                                    $shortDesc = explode('.', $solution->short_description);
                                    $features = array_filter(array_map('trim', $shortDesc));
                                    $features = array_slice($features, 0, 6);
                                }

                                // Nếu vẫn không có, hiển thị 6 tính năng mẫu
                                if (empty($features)) {
                                    $features = [
                                        'Dung lượng: 5GB',
                                        'Băng thông: Không giới hạn',
                                        'Database: 3',
                                        'Email: 5 tài khoản',
                                        'SSL: Miễn phí',
                                        'Backup: Hàng tuần',
                                    ];
                                }
                            @endphp

                            <ul class="price_features">
                                @foreach ($features as $feature)
                                    <li>
                                        <i class="fa fa-check-circle text-success mr-2"></i> {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="btn-box 2">
                            @if (isset($solution->categoryObject) && $solution->categoryObject)
                                <a href="{{ route('category.detail', $solution->categoryObject->slug) }}"
                                    class="btn-detail">
                                    <span>Xem chi tiết</span> <i class="fa fa-arrow-right ml-2"></i>
                                </a>
                            @else
                                <a href="{{ route('service.detail', $solution->slug) }}" class="btn-detail">
                                    <span>Xem chi tiết</span> <i class="fa fa-arrow-right ml-2"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <!-- Hiển thị dữ liệu mẫu nếu không có sản phẩm nào -->
                    <div class="box">
                        <div class="detail-box">
                            <div class="price-badge">
                                <h2>199.000 <span>Đ</span></h2>
                                <div class="period-tag">Tháng</div>
                            </div>

                            <h6 class="package-title">Hosting Cơ bản</h6>

                            <ul class="price_features">
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Dung lượng: 5GB</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Băng thông: Không giới hạn</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Database: 3</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Email: 5 tài khoản</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> SSL: Miễn phí</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Backup: Hàng tuần</li>
                            </ul>
                        </div>
                        <div class="btn-box">
                            <a href="#" class="btn-detail">
                                <span>Xem chi tiết</span> <i class="fa fa-arrow-right ml-2"></i>
                            </a>
                            <a href="#" class="btn-buy">
                                <i class="fa fa-shopping-cart mr-2"></i> <span>Đặt mua</span>
                            </a>
                        </div>
                    </div>

                    <div class="box featured">
                        <div class="ribbon"><span>Phổ biến</span></div>
                        <div class="detail-box">
                            <div class="price-badge">
                                <h2>349.000 <span>Đ</span></h2>
                                <div class="period-tag">Tháng</div>
                            </div>

                            <h6 class="package-title">Hosting Doanh nghiệp</h6>

                            <ul class="price_features">
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Dung lượng: 10GB</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Băng thông: Không giới hạn</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Database: 10</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Email: 20 tài khoản</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> SSL: Miễn phí</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Backup: Hàng ngày</li>
                            </ul>
                        </div>
                        <div class="btn-box">
                            <a href="#" class="btn-detail">
                                <span>Xem chi tiết</span> <i class="fa fa-arrow-right ml-2"></i>
                            </a>
                            <a href="#" class="btn-buy">
                                <i class="fa fa-shopping-cart mr-2"></i> <span>Đặt mua</span>
                            </a>
                        </div>
                    </div>

                    <div class="box">
                        <div class="detail-box">
                            <div class="price-badge">
                                <h2>899.000 <span>Đ</span></h2>
                                <div class="period-tag">Tháng</div>
                            </div>

                            <h6 class="package-title">Hosting Cao cấp</h6>

                            <ul class="price_features">
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Dung lượng: 30GB</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Băng thông: Không giới hạn</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Database: Không giới hạn</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Email: Không giới hạn</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> SSL: Wildcard</li>
                                <li><i class="fa fa-check-circle text-success mr-2"></i> Backup: Tự động mỗi giờ</li>
                            </ul>
                        </div>
                        <div class="btn-box">
                            <a href="#" class="btn-detail">
                                <span>Xem chi tiết</span> <i class="fa fa-arrow-right ml-2"></i>
                            </a>
                            <a href="#" class="btn-buy">
                                <i class="fa fa-shopping-cart mr-2"></i> <span>Đặt mua</span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- price section -->

    <!-- client section -->
    <section class="client_section ">
        <div class="container">
            <div class="heading_container heading_center">
                <h2>
                    Testimonial
                </h2>
                <p>
                    Even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to
                </p>
            </div>
        </div>
        <div class="container px-0">
            <div id="customCarousel2" class="carousel  slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-10 mx-auto">
                                    <div class="box">
                                        <div class="img-box">
                                            <img src="{{ asset('assets/web/hostit/images/client.jpg') }}" alt="">
                                        </div>
                                        <div class="detail-box">
                                            <div class="client_info">
                                                <div class="client_name">
                                                    <h5>
                                                        Morojink
                                                    </h5>
                                                    <h6>
                                                        Customer
                                                    </h6>
                                                </div>
                                                <i class="fa fa-quote-left" aria-hidden="true"></i>
                                            </div>
                                            <p>
                                                I've been using this hosting service for my e-commerce business for over two
                                                years now, and I
                                                couldn't be happier with my decision. The performance is outstanding - my
                                                website loads quickly
                                                even during peak traffic times, which has significantly improved my
                                                conversion rates. Their
                                                technical support team deserves special praise for their prompt responses
                                                and expert solutions
                                                whenever I've needed assistance.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-10 mx-auto">
                                    <div class="box">
                                        <div class="img-box">
                                            <img src="{{ asset('assets/web/hostit/images/client.jpg') }}" alt="">
                                        </div>
                                        <div class="detail-box">
                                            <div class="client_info">
                                                <div class="client_name">
                                                    <h5>
                                                        Morojink
                                                    </h5>
                                                    <h6>
                                                        Customer
                                                    </h6>
                                                </div>
                                                <i class="fa fa-quote-left" aria-hidden="true"></i>
                                            </div>
                                            <p>
                                                Their WordPress hosting is top-notch. I’ve been using it for over 6 months
                                                now and haven’t had
                                                any downtime. The dashboard is easy to navigate, and I love the automatic
                                                backups. Very
                                                satisfied!
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-10 mx-auto">
                                    <div class="box">
                                        <div class="img-box">
                                            <img src="{{ asset('assets/web/hostit/images/client.jpg') }}" alt="">
                                        </div>
                                        <div class="detail-box">
                                            <div class="client_info">
                                                <div class="client_name">
                                                    <h5>
                                                        Morojink
                                                    </h5>
                                                    <h6>
                                                        Customer
                                                    </h6>
                                                </div>
                                                <i class="fa fa-quote-left" aria-hidden="true"></i>
                                            </div>
                                            <p>
                                                “We use their VPS hosting for our internal CRM system—super stable and fast.
                                                The tech support
                                                responds quickly and speaks both English and Vietnamese. Highly recommended
                                                for developers or
                                                startups.”
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="carousel_btn-box">
                    <a class="carousel-control-prev" href="#customCarousel2" role="button" data-slide="prev">
                        <i class="fa fa-angle-left" aria-hidden="true"></i>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#customCarousel2" role="button" data-slide="next">
                        <i class="fa fa-angle-right" aria-hidden="true"></i>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- end client section -->

    <!-- contact section -->
    <section class="contact_section layout_padding-bottom">
        <div class="container">
            <div class="heading_container heading_center">
                <h2>
                    Get In Touch
                </h2>
            </div>
            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <div class="form_container">
                        <form action="">
                            <div>
                                <input type="text" placeholder="Your Name" />
                            </div>
                            <div>
                                <input type="email" placeholder="Your Email" />
                            </div>
                            <div>
                                <input type="text" placeholder="Your Phone" />
                            </div>
                            <div>
                                <input type="text" class="message-box" placeholder="Message" />
                            </div>
                            <div class="btn_box ">
                                <button>
                                    SEND
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end contact section -->

    <!-- info section -->

@endsection

@push('header_css')
    <style>
        .price_container .box {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            margin-bottom: 30px;
        }

        .price_container .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .price_container .box.featured {
            border: 2px solid #4154f1;
            transform: scale(1.03);
            z-index: 1;
        }

        .price_container .ribbon {
            position: absolute;
            top: 20px;
            right: -30px;
            transform: rotate(45deg);
            width: 150px;
            background: #4154f1;
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .price_container .ribbon span {
            display: block;
            padding: 5px 0;
        }

        .price-badge {
            text-align: center;
            padding: 20px 0;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 0 0 50% 50% / 20px;
            margin-bottom: 15px;
        }

        .price-badge h2 {
            font-size: 32px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .price-badge h2 span {
            font-size: 20px;
            font-weight: 600;
        }

        .period-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .original-price {
            font-size: 16px;
            opacity: 0.7;
            margin-top: 5px;
        }

        .package-title {
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            margin: 15px 0;
            color: #333;
        }

        .price_features {
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .price_features li {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            color: #555;
            display: flex;
            align-items: center;
        }

        .price_features li:last-child {
            border-bottom: none;
        }

        .price_features i {
            color: #10b981;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .btn-box 2 {
            display: flex;
            flex-direction: column;
            padding: 15px 20px;
            background: #f8f9fa;
            gap: 10px;
        }

        .btn-detail,
        .btn-buy {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-detail {
            background: transparent;
            color: #4154f1;
            border: 1px solid #4154f1;
        }

        .btn-buy {
            background: #4154f1;
            color: white;
        }

        .btn-detail:hover {
            background: rgba(65, 84, 241, 0.1);
        }

        .btn-buy:hover {
            background: #2a3bf1;
        }

        @media (max-width: 768px) {
            .price_container .box.featured {
                transform: none;
            }
        }
    </style>
@endpush
