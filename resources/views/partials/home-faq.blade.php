<section class="container faq-section" id="faq" aria-labelledby="faq-title">
    <div class="faq-heading"><p class="eyebrow">{{ __('home_sections.faq_kicker') }}</p><h2 id="faq-title">{{ __('home_sections.faq_title') }}</h2><p class="section-intro">{{ __('home_sections.faq_intro') }}</p><a class="text-link" href="{{ route('customer.register') }}">{{ __('home.step_1') }}<svg class="icon" aria-hidden="true"><use href="#arrow"/></svg></a></div>
    <div class="faq-list">
        @foreach(range(1, 6) as $number)
        <details class="faq-item"><summary>{{ __('home_sections.question_'.$number) }}<span class="faq-toggle" aria-hidden="true"></span></summary><div class="faq-answer"><p>{{ __('home_sections.answer_'.$number) }}</p></div></details>
        @endforeach
    </div>
</section>
