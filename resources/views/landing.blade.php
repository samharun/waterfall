<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('home.title') }}</title>
    <meta name="description" content="{{ __('home.description') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing-home.css') }}?v=3">
    <script src="{{ asset('js/landing-typing.js') }}?v=2" defer></script>
    <link rel="stylesheet" href="{{ asset('css/home-sections.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/pricing-showcase.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/page-refinement.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/step-art.css') }}?v=1">
</head>
<body>
<svg class="icon-library" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <symbol id="drop" viewBox="0 0 32 40"><path d="M16 1C12 9 2 18 2 25a14 14 0 0 0 28 0C30 18 20 9 16 1Z" fill="#008cdd"/><path d="M16 9C13 16 8 20 8 25a8 8 0 0 0 8 8c-7-6-2-13 0-24Z" fill="#c8f4ff"/><path d="M16 1c4 8 14 17 14 24a14 14 0 0 1-14 14c12-9 7-20 0-38Z" fill="#0061b6"/></symbol>
    <symbol id="arrow" viewBox="0 0 24 24"><path d="M4 12h16m-6-6 6 6-6 6"/></symbol>
    <symbol id="pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></symbol>
    <symbol id="truck" viewBox="0 0 24 24"><path d="M3 5h11v13H3V5Zm11 5h4l4 4v4h-8M1 9h7M1 13h5"/><circle cx="7" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></symbol>
    <symbol id="phone" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="3"/><path d="M10 5h4m-3 14h2m-5-7 3 3 5-6"/></symbol>
    <symbol id="calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="17" rx="2"/><path d="M7 2v6m10-6v6M3 11h18m-13 4h1m6 0h1m-8 4h1m6 0h1"/></symbol>
    <symbol id="jar" viewBox="0 0 24 24"><path d="M9 2h6v4l4 4v10a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V10l4-4V2Zm0 4h6M5 12h14M5 17h14M9 2h6"/></symbol>
</svg>
<a class="skip-link" href="#main">{{ __('home.skip') }}</a>
<header class="header">
    <div class="container header-inner">
        <a class="brand" href="{{ route('landing') }}" aria-label="Waterfall {{ __('home.home') }}"><img class="brand-logo" src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall" width="555" height="450"></a>
        <nav class="desktop-nav" aria-label="{{ __('home.navigation') }}">
            <a href="#home">{{ __('home.home') }}</a><a href="#pricing">{{ app()->getLocale() === 'bn' ? 'পানির মূল্য' : 'Pricing' }}</a><a href="#how-it-works">{{ __('home.how') }}</a><a href="#service-areas">{{ __('home.areas') }}</a>
        </nav>
        <div class="header-actions">
            <div class="languages" aria-label="{{ __('home.language') }}"><a href="{{ route('locale.switch', 'bn') }}" lang="bn" @if(app()->getLocale() === 'bn') aria-current="true" @endif>বাং</a><span>/</span><a href="{{ route('locale.switch', 'en') }}" lang="en" @if(app()->getLocale() === 'en') aria-current="true" @endif>EN</a></div>
            <a class="login" href="{{ route('customer.login') }}">{{ __('home.login') }}</a>
            <a class="button button-small" href="{{ route('customer.register') }}">{{ __('home.order') }}</a>
        </div>
    </div>
<nav class="section-jumps" aria-label="{{ __('home.navigation') }}"><a href="#pricing">{{ app()->getLocale() === 'bn' ? 'পানির মূল্য' : 'Pricing' }}</a><a href="#how-it-works">{{ __('home.how') }}</a><a href="#service-areas">{{ __('home.areas') }}</a><a href="#faq">{{ app()->getLocale() === 'bn' ? 'প্রশ্নোত্তর' : 'FAQs' }}</a></nav></header>
<main id="main">
    <section class="hero" id="home">
        <div class="hero-photo"><img src="{{ asset('images/waterfall-home-hero-branded.jpg') }}" alt="{{ __('home.hero_alt') }}" fetchpriority="high" width="1536" height="1024"></div>
        <div class="container hero-inner">
            <div class="hero-copy">
                <p class="eyebrow location"><svg class="icon"><use href="#pin"/></svg>{{ __('home.location') }}</p>
                <h1>{{ __('home.headline') }}<br><span>{{ __('home.headline_accent') }}</span></h1>
                <p class="hero-description">{{ __('home.description') }}</p>
                <div class="hero-buttons"><a class="button" href="{{ route('customer.register') }}">{{ __('home.order_water') }}<svg class="icon"><use href="#arrow"/></svg></a><a class="text-link" href="#how-it-works"><span class="play" aria-hidden="true">▷</span>{{ __('home.how') }}</a></div>
                <p class="coverage-note"><svg class="icon"><use href="#pin"/></svg>{{ __('home.coverage_note') }}</p>
            </div>
            <div class="delivery-note"><svg class="icon"><use href="#truck"/></svg><div><strong>{{ __('home.delivery_time') }}</strong><span>{{ __('home.slots') }}</span></div></div>
        </div>
    </section>
    <section class="benefits" id="features" aria-label="{{ __('home.benefits') }}">
        <div class="container benefit-grid">
            @foreach(['phone', 'pin', 'calendar'] as $icon)
            <div class="benefit"><svg class="icon"><use href="#{{ $icon }}"/></svg><div><h2>{{ __('home.benefit_'. $loop->iteration) }}</h2><p>{{ __('home.benefit_desc_'. $loop->iteration) }}</p></div></div>
            @endforeach
        </div>
    </section>
    @include('partials.home-pricing')
    <section class="steps container" id="how-it-works">
        <div class="section-heading"><p class="eyebrow">{{ __('home.three_steps') }}</p><h2>{{ __('home.steps_title') }}</h2></div>
        <div class="step-grid">
            @foreach(['register', 'schedule', 'delivery'] as $icon)
            <article class="step"><div class="step-illustration"><span class="step-number">0{{ $loop->iteration }}</span><img class="step-art" src="{{ asset('images/steps/' . $icon . '.svg') }}" alt="" width="240" height="170" loading="lazy"></div><h3>{{ __('home.step_'. $loop->iteration) }}</h3><p>{{ __('home.step_desc_'. $loop->iteration) }}</p>@unless($loop->last)<svg class="icon step-arrow" aria-hidden="true"><use href="#arrow"/></svg>@endunless</article>
            @endforeach
        </div>
    </section>
    <section class="container area-section" id="service-areas">
        <div class="area-panel"><div class="area-copy"><p class="eyebrow">{{ __('home.areas') }}</p><h2>{{ __('home.area_title') }}</h2><p>{{ __('home.area_subtitle') }}</p><div class="area-chips"><span><svg class="icon"><use href="#pin"/></svg>{{ __('home.chawkbazar') }}</span><span><svg class="icon"><use href="#pin"/></svg>{{ __('home.bakolia') }}</span></div></div>
            <div class="area-map" role="img" aria-label="{{ __('home.map_alt') }}"><svg class="map-art" viewBox="0 0 600 360" preserveAspectRatio="xMidYMid slice" aria-hidden="true"><defs><pattern id="streets" width="85" height="65" patternUnits="userSpaceOnUse" patternTransform="rotate(-23)"><rect width="85" height="65" fill="#e8f2f6"/><path d="M0 0H85V65H0Z M0 25h85M35 0v65" fill="none" stroke="white" stroke-width="4"/></pattern></defs><rect width="600" height="360" fill="url(#streets)"/><path d="M-40 300C130 155 240 385 400 255S560 235 650 190" fill="none" stroke="#b0e0fa" stroke-width="76"/><path d="M40-30C230 130 320 100 620 60M150-20C90 120 280 180 380 410" fill="none" stroke="white" stroke-width="10"/><path d="m430 30 55-15 12 49-52 16Zm-280 90 34-14 12 38-30 11Z" fill="#c9e8d8"/></svg><div class="map-pin pin-one"><svg class="icon"><use href="#pin"/></svg><span>{{ __('home.chawkbazar') }}</span></div><div class="map-pin pin-two"><svg class="icon"><use href="#pin"/></svg><span>{{ __('home.bakolia') }}</span></div><small>{{ __('home.map_note') }}</small></div>
        </div>
    </section>
    @include('partials.home-faq')
    <section class="container cta-section" id="download"><div class="cta"><div><h2>{{ __('home.cta_title') }}</h2><p>{{ __('home.cta_description') }}</p></div><a class="button button-white" href="{{ route('customer.register') }}">{{ __('home.order') }}<svg class="icon"><use href="#arrow"/></svg></a></div></section>
</main>
<footer class="container footer"><div><a class="brand" href="#home"><img class="brand-logo" src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall" width="555" height="450"></a><p>© {{ date('Y') }} Waterfall. {{ __('home.copyright') }}</p></div><nav aria-label="{{ __('home.footer_navigation') }}"><a href="#pricing">{{ app()->getLocale() === 'bn' ? 'পানির মূল্য' : 'Pricing' }}</a><a href="#faq">{{ app()->getLocale() === 'bn' ? 'প্রশ্নোত্তর' : 'FAQs' }}</a><a href="#home">{{ __('home.home') }}</a><a href="#how-it-works">{{ __('home.how') }}</a><a href="#service-areas">{{ __('home.areas') }}</a><a href="{{ route('customer.login') }}">{{ __('home.login') }}</a></nav></footer>
</body>
</html>
