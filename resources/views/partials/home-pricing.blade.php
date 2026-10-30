@php
    $publicJars = \App\Models\Product::active()->where('product_type', 'jar')->orderBy('default_price')->get(['name', 'default_price', 'deposit_amount']);
    $money = fn ($value) => app()->getLocale() === 'bn'
        ? strtr(number_format((float) $value, 2), ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'])
        : number_format((float) $value, 2);
@endphp
<section class="pricing-section" id="pricing" aria-labelledby="pricing-title">
    <div class="container">
        <div class="section-heading"><p class="eyebrow">{{ __('home_sections.pricing_kicker') }}</p><h2 id="pricing-title">{{ __('home_sections.pricing_title') }}</h2><p class="section-intro">{{ __('home_sections.pricing_intro') }}</p></div>
        <div class="pricing-grid">
            @forelse($publicJars as $jar)
            <article class="price-card price-showcase">
                <div class="product-visual">
                    <img src="{{ asset('images/waterfall-product-studio.jpg') }}" alt="{{ $jar->name }} — Waterfall" width="1254" height="1254" loading="lazy">
                    <span class="product-badge"><svg class="icon" aria-hidden="true"><use href="#drop"/></svg>Waterfall</span>
                    <div class="product-caption"><svg class="icon" aria-hidden="true"><use href="#truck"/></svg><div><strong>{{ __('home.delivery_time') }}</strong><span>{{ __('home.slots') }}</span></div></div>
                </div>
                <div class="product-info">
                <p class="product-kicker">{{ __('home_sections.pricing_kicker') }}</p>
                <h3>{{ app()->getLocale() === 'bn' && $jar->name === '20 Liter Water Jar' ? '২০ লিটার পানির জার' : $jar->name }}</h3>
                <p class="product-description">{{ __('home.description') }}</p>
                <div class="product-price-box">
                <p class="price-label">{{ __('home_sections.standard_rate') }}</p>
                <p class="price-amount">৳{{ $money($jar->default_price) }}<span>/ {{ __('home_sections.per_jar') }}</span></p>
                </div>
                <dl class="price-details"><div><dt>{{ __('home_sections.deposit') }}</dt><dd>৳{{ $money($jar->deposit_amount) }}</dd></div><div><dt>{{ __('home_sections.delivery_charge') }}</dt><dd>{{ __('home_sections.confirm_charge') }}</dd></div></dl>
                <a class="button" href="{{ route('customer.register') }}">{{ __('home.order_water') }}<svg class="icon" aria-hidden="true"><use href="#arrow"/></svg></a>
                <a class="product-login" href="{{ route('customer.login') }}">{{ __('home_sections.existing_customer') }} →</a>
                </div>
            </article>
            @empty
            <article class="price-card price-empty"><div class="price-icon"><svg class="icon" aria-hidden="true"><use href="#jar"/></svg></div><h3>{{ __('home_sections.empty_title') }}</h3><p>{{ __('home_sections.empty_description') }}</p><a class="button" href="{{ route('customer.register') }}">{{ __('home.step_1') }}</a></article>
            @endforelse
        </div>
        <p class="pricing-note">{{ __('home_sections.pricing_note') }}</p>
    </div>
</section>
