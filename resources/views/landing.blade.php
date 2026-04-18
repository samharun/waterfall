<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Waterfall - Pure Drinking Water Delivery</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-blue: #0d6efd;
            --deep-blue: #0647a9;
            --sky-blue: #35c6f4;
            --dark-text: #0f172a;
            --muted-text: #64748b;
            --light-bg: #f8fafc;
            --border-color: #e5e7eb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: var(--dark-text);
            background: #ffffff;
        }

        a {
            text-decoration: none;
        }

        /* ================================
           Navbar
        ================================ */

        .navbar {
            padding: 8px 0;
            background: linear-gradient(135deg, #0a2d66 0%, #0647a9 100%);
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 12px rgba(6, 71, 169, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 24px;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 2px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand img {
            height: 55px;
            width: auto;
            object-fit: contain;
            transition: height 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .navbar-brand span {
            display: none;
        }

        .navbar-brand i {
            font-size: 28px;
        }

        @media (min-width: 576px) {
            .navbar-brand img {
                height: 60px;
            }
        }

        @media (min-width: 992px) {
            .navbar-brand img {
                height: 65px;
            }
        }

        .navbar-nav .nav-link {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-left: 12px;
            transition: all 0.3s ease;
            position: relative;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        .navbar-nav .nav-link:hover {
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .navbar-nav .nav-link::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 12px;
            width: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.8);
            transition: width 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after {
            width: calc(100% - 24px);
        }

        .nav-login-btn {
            border-radius: 999px;
            padding: 8px 20px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.95);
            color: #0647a9;
            border: 2px solid rgba(255, 255, 255, 0.95);
            transition: all 0.3s ease;
            text-shadow: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-login-btn:hover {
            background: #ffffff;
            color: #0647a9;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255,255,255,0.9)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* ================================
           Hero Section - Compact Version
        ================================ */

        .hero-section {
            min-height: calc(90vh - 0px);
            padding: 0px 0 0px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(53, 198, 244, 0.15), transparent 35%),
                linear-gradient(135deg, rgba(6, 71, 169, 0.65) 0%, rgba(2, 43, 104, 0.70) 55%, rgba(0, 20, 60, 0.75) 100%),
                url('{{ asset('images/waterfall-hero-bg.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #ffffff;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            left: -140px;
            bottom: -180px;
        }

        .hero-section::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            right: 8%;
            top: 12%;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.25);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(10px);
            color: #ffffff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .hero-title {
            font-size: 42px;
            line-height: 1.15;
            font-weight: 800;
            margin-bottom: 18px;
            max-width: 720px;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), 0 2px 6px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 17px;
            line-height: 1.55;
            margin-bottom: 24px;
            max-width: 680px;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .hero-actions .btn {
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 16px;
        }

        /* ================================
           Phone Mockup - Compact Version
        ================================ */

        .phone-mockup {
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
            border-radius: 36px;
            background: rgba(255, 255, 255, 0.18);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
            position: relative;
            z-index: 3;
        }

        .phone-screen {
            padding: 22px 18px;
            border-radius: 28px;
            background: #ffffff;
            color: #1f2937;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
        }

        .phone-top-bar {
            width: 72px;
            height: 5px;
            border-radius: 999px;
            margin: 0 auto 18px;
            background: #d1d5db;
        }

        .phone-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .phone-subtitle {
            font-size: 13px;
            margin-bottom: 16px;
            color: #64748b;
        }

        .order-circle {
            width: 125px;
            height: 125px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, #0d6efd, #35c6f4);
            color: #ffffff;
            box-shadow: 0 14px 30px rgba(13, 110, 253, 0.28);
        }

        .order-circle h1 {
            font-size: 36px;
        }

        .order-circle small {
            font-size: 12px;
            opacity: 0.95;
        }

        .phone-info-row {
            padding: 10px 12px;
            margin-bottom: 10px;
            font-size: 14px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            color: #334155;
        }

        .phone-info-row strong {
            color: #0f172a;
            font-size: 14px;
        }

        .phone-info-icon {
            width: 30px;
            height: 30px;
            font-size: 14px;
            margin-right: 8px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e7f1ff;
            color: #0d6efd;
        }

        .phone-route-btn {
            padding: 10px 14px;
            font-size: 14px;
            border-radius: 14px;
            font-weight: 700;
        }

        /* ================================
           Generic Sections
        ================================ */

        .section-padding {
            padding: 70px 0;
        }

        .section-title {
            font-size: 34px;
            font-weight: 800;
            margin-bottom: 12px;
            color: var(--dark-text);
        }

        .section-subtitle {
            font-size: 16px;
            color: var(--muted-text);
            max-width: 700px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        .feature-card {
            height: 100%;
            padding: 28px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
            transition: 0.25s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 42px rgba(15, 23, 42, 0.1);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: #e7f1ff;
            color: var(--primary-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 18px;
        }

        .feature-card h5 {
            font-weight: 800;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: var(--muted-text);
            margin-bottom: 0;
            line-height: 1.6;
        }

        .light-section {
            background: var(--light-bg);
        }

        /* ================================
           How It Works
        ================================ */

        .step-card {
            padding: 26px;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            height: 100%;
        }

        .step-number {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--primary-blue);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .step-card h5 {
            font-weight: 800;
        }

        .step-card p {
            color: var(--muted-text);
            margin-bottom: 0;
        }

        /* ================================
           CTA
        ================================ */

        .cta-section {
            padding: 70px 0;
            background: linear-gradient(135deg, #0647a9, #0d6efd);
            color: #ffffff;
        }

        .cta-box {
            max-width: 780px;
            margin: 0 auto;
            text-align: center;
        }

        /* ================================
           Promotion Banner
        ================================ */

        .promo-section {
            padding: 70px 0;
            background: linear-gradient(135deg, #eff7ff, #ffffff);
        }

        .promo-card {
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 26px 60px rgba(13, 110, 253, 0.12);
            padding: 42px;
        }

        .promo-title {
            font-size: 42px;
            font-weight: 800;
            line-height: 1.05;
            margin-bottom: 18px;
            color: #0d3c75;
        }

        .promo-subtitle {
            font-size: 18px;
            line-height: 1.75;
            color: #475569;
            max-width: 630px;
            margin-bottom: 30px;
        }

        .promo-image {
            width: 100%;
            max-width: 520px;
            border-radius: 26px;
            box-shadow: 0 22px 45px rgba(13, 110, 253, 0.18);
        }

        .promo-actions .btn {
            min-width: 170px;
        }

        @media (max-width: 991px) {
            .promo-card {
                padding: 30px;
            }

            .promo-title {
                font-size: 32px;
            }

            .promo-image {
                margin-top: 28px;
            }
        }

        .cta-box h2 {
            font-size: 34px;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .cta-box p {
            color: rgba(255, 255, 255, 0.86);
            margin-bottom: 28px;
        }

        /* ================================
           Footer
        ================================ */

        .footer {
            padding: 28px 0;
            background: #020617;
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
        }

        .footer strong {
            color: #ffffff;
        }

        /* ================================
           Responsive Design
        ================================ */

        @media (max-width: 991px) {
            .hero-section {
                min-height: auto;
                padding: 45px 0 35px;
                text-align: center;
            }

            .hero-title {
                font-size: 34px;
                margin-left: auto;
                margin-right: auto;
            }

            .hero-subtitle {
                font-size: 16px;
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
            }

            .hero-actions {
                justify-content: center;
            }

            .phone-mockup {
                max-width: 270px;
                margin-top: 35px;
            }

            .phone-screen {
                padding: 20px 16px;
            }

            .order-circle {
                width: 110px;
                height: 110px;
            }

            .order-circle h1 {
                font-size: 32px;
            }

            .phone-info-row {
                padding: 9px 10px;
                font-size: 13px;
            }

            .phone-info-icon {
                width: 28px;
                height: 28px;
                font-size: 13px;
            }

            .section-padding {
                padding: 55px 0;
            }

            .section-title {
                font-size: 28px;
            }
        }

        @media (max-width: 575px) {
            .hero-section {
                padding: 35px 0 30px;
            }

            .hero-badge {
                font-size: 13px;
                padding: 7px 13px;
            }

            .hero-title {
                font-size: 29px;
            }

            .hero-subtitle {
                font-size: 15px;
                line-height: 1.5;
            }

            .hero-actions .btn {
                width: 100%;
                font-size: 15px;
            }

            .phone-mockup {
                max-width: 250px;
                margin-top: 28px;
            }

            .phone-title {
                font-size: 17px;
            }

            .phone-subtitle {
                font-size: 12px;
            }

            .order-circle {
                width: 100px;
                height: 100px;
                margin-bottom: 15px;
            }

            .order-circle h1 {
                font-size: 28px;
            }

            .phone-route-btn {
                font-size: 13px;
            }

            .navbar-brand {
                font-size: 21px;
            }

            .navbar-nav .nav-link {
                padding: 8px 0;
                margin-left: 0;
            }

            .navbar-nav .nav-link::after {
                display: none;
            }

            .nav-login-btn {
                width: 100%;
                margin-top: 12px;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

{{-- Navbar --}}
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <!-- Logo will display here once image is added -->
            <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="navbar-logo">
            <!-- Fallback icon -->
            <i class="bi bi-droplet-fill" style="display: none;"></i>
            <span>Waterfall</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="mainNavbar" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="#home">{{ __('landing.nav_home') }}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#features">{{ __('landing.nav_features') }}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">{{ __('landing.nav_how_it_works') }}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#download">{{ __('landing.nav_download') }}</a>
                </li>

                <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                    <div class="d-flex gap-2">
                        <a href="{{ route('locale.switch', 'en') }}" class="btn btn-sm btn-outline-light {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                        <a href="{{ route('locale.switch', 'bn') }}" class="btn btn-sm btn-outline-light {{ app()->getLocale() === 'bn' ? 'active' : '' }}">বাং</a>
                    </div>
                </li>

                <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                    <a href="{{ url('customer/login') }}" class="btn btn-outline-primary nav-login-btn">
                        {{ __('landing.login') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Hero --}}
<section id="home" class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <i class="bi bi-shield-check"></i>
                    {{ __('landing.hero_badge') }}
                </div>

                <h1 class="hero-title">
                    {{ __('landing.hero_title') }}
                </h1>

                <p class="hero-subtitle">
                    {{ __('landing.hero_subtitle') }}
                </p>

                <div class="hero-actions d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="{{ url('customer/register') }}"
                       class="btn btn-light btn-lg rounded-pill px-4 fw-bold">
                        <i class="bi bi-person-plus me-2"></i>{{ __('landing.register_customer') }}
                    </a>

                    <a href="#download"
                       class="btn btn-primary btn-lg rounded-pill px-4 fw-bold border border-light">
                        <i class="bi bi-download me-2"></i>{{ __('landing.download_app') }}
                    </a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="phone-mockup">
                    <div class="phone-screen">
                        <div class="phone-top-bar"></div>

                        <h5 class="phone-title">{{ __('landing.my_water_order') }}</h5>
                        <p class="phone-subtitle">{{ __('landing.customer_id') }}: WF-CUS-000124</p>

                        <div class="order-circle">
                            <div>
                                <h1 class="fw-bold mb-0">2</h1>
                                <small>{{ __('landing.jars_ordered') }}</small>
                            </div>
                        </div>

                        <div class="phone-info-row d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="phone-info-icon">
                                    <i class="bi bi-truck"></i>
                                </span>
                                <span>{{ __('landing.order_status') }}</span>
                            </div>
                            <strong>{{ __('landing.on_the_way') }}</strong>
                        </div>

                        <div class="phone-info-row d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="phone-info-icon">
                                    <i class="bi bi-clock-history"></i>
                                </span>
                                <span>{{ __('landing.delivery_slot') }}</span>
                            </div>
                            <strong>{{ __('landing.morning') }}</strong>
                        </div>

                        <div class="phone-info-row d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="phone-info-icon">
                                    <i class="bi bi-droplet"></i>
                                </span>
                                <span>{{ __('landing.jar_balance') }}</span>
                            </div>
                            <strong>{{ __('landing.jars_count') }}</strong>
                        </div>

                        <div class="phone-info-row d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="phone-info-icon">
                                    <i class="bi bi-wallet2"></i>
                                </span>
                                <span>{{ __('landing.current_due') }}</span>
                            </div>
                            <strong>{{ __('landing.amount') }}</strong>
                        </div>

                        <button type="button" class="btn btn-primary w-100 phone-route-btn mt-3">
                            <i class="bi bi-plus-circle me-2"></i>{{ __('landing.order_water_jar') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section id="features" class="section-padding">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">{{ __('landing.features_title') }}</h2>
            <p class="section-subtitle">
                {{ __('landing.features_subtitle') }}
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <h5>{{ __('landing.feature_customer_order_title') }}</h5>
                    <p>
                        {{ __('landing.feature_customer_order_desc') }}
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5>{{ __('landing.feature_delivery_status_title') }}</h5>
                    <p>
                        {{ __('landing.feature_delivery_status_desc') }}
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h5>{{ __('landing.feature_monthly_billing_title') }}</h5>
                    <p>
                        {{ __('landing.feature_monthly_billing_desc') }}
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-droplet-half"></i>
                    </div>
                    <h5>{{ __('landing.feature_jar_deposit_title') }}</h5>
                    <p>
                        {{ __('landing.feature_jar_deposit_desc') }}
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5>{{ __('landing.feature_zone_delivery_title') }}</h5>
                    <p>
                        {{ __('landing.feature_zone_delivery_desc') }}
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <h5>{{ __('landing.feature_office_dealer_title') }}</h5>
                    <p>
                        {{ __('landing.feature_office_dealer_desc') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section id="how-it-works" class="section-padding light-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">{{ __('landing.how_it_works_title') }}</h2>
            <p class="section-subtitle">
                {{ __('landing.how_it_works_subtitle') }}
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>{{ __('landing.step_register_title') }}</h5>
                    <p>
                        {{ __('landing.step_register_desc') }}
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>{{ __('landing.step_order_title') }}</h5>
                    <p>
                        {{ __('landing.step_order_desc') }}
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>{{ __('landing.step_delivery_title') }}</h5>
                    <p>
                        {{ __('landing.step_delivery_desc') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Download / CTA --}}
<section id="download" class="cta-section">
    <div class="container">
        <div class="cta-box">
            <h2>{{ __('landing.cta_title') }}</h2>
            <p>
                {{ __('landing.cta_subtitle') }}
            </p>

            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a href="{{ url('customer/register') }}"
                   class="btn btn-light btn-lg rounded-pill px-4 fw-bold">
                    <i class="bi bi-person-plus me-2"></i>{{ __('landing.register_now') }}
                </a>

                <a href="{{ url('customer/login') }}"
                   class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('landing.login') }}
                </a>
            </div>
        </div>
    </div>
</section>

<section class="promo-section">
    <div class="container">
        <div class="promo-card">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6">
                    <h2 class="promo-title">{{ __('landing.footer_promo_title') }}</h2>
                    <p class="promo-subtitle">{{ __('landing.footer_promo_subtitle') }}</p>
                    <div class="d-flex flex-wrap gap-3 promo-actions">
                        <a href="{{ url('customer/register') }}" class="btn btn-primary btn-lg rounded-pill">
                            {{ __('landing.footer_promo_order_now') }}
                        </a>
                        <a href="#features" class="btn btn-outline-primary btn-lg rounded-pill">
                            {{ __('landing.footer_promo_learn_more') }}
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="{{ asset('images/jar.png') }}" alt="Water Delivery" class="promo-image img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="footer">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div>
                <strong>{{ __('landing.footer_brand') }}</strong>
            </div>

            <div>
                {{ __('landing.footer_copyright', ['year' => date('Y')]) }}
            </div>
        </div>
    </div>
</footer>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Typewriter Effect --}}
<script>
    class Typewriter {
        constructor(element, options = {}) {
            this.element = element;
            this.text = element.textContent;
            this.speed = options.speed || 50;
            this.delay = options.delay || 0;
            this.repeatDelay = options.repeatDelay || 5000; // 5 seconds
            this.index = 0;
            this.isTyping = false;
            
            // Store original text and clear element
            element.textContent = '';
            this.element.style.minHeight = this.element.style.minHeight || 'auto';
        }

        start() {
            setTimeout(() => this.type(), this.delay);
        }

        type() {
            if (this.index < this.text.length) {
                // Add character
                this.element.textContent += this.text.charAt(this.index);
                this.index++;
                
                // Vary speed slightly for natural effect
                const variance = Math.random() * 30;
                const speed = this.speed + (Math.random() > 0.8 ? variance : -variance / 2);
                
                setTimeout(() => this.type(), speed);
            } else if (!this.isTyping) {
                // Text finished typing, wait and restart
                this.isTyping = true;
                setTimeout(() => {
                    this.element.textContent = '';
                    this.index = 0;
                    this.isTyping = false;
                    this.type();
                }, this.repeatDelay);
            }
        }
    }

    // Initialize typewriter when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const heroTitle = document.querySelector('.hero-title');
        if (heroTitle) {
            const typewriter = new Typewriter(heroTitle, {
                speed: 45,
                delay: 300,
                repeatDelay: 5000 // 5 seconds between repeats
            });
            typewriter.start();
        }
    });
</script>

</body>
</html>