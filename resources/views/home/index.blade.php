@extends('layouts.public')

@php
    $cityContext = $cityContext ?? ['isFallback' => false, 'activeCityName' => request('city') ?? session('user_city'), 'launchingSoonCityName' => null, 'defaultCityName' => 'Bhopal'];
    $homeCity = $cityContext['activeCityName'] ?? request('city') ?? session('user_city');
    $displayCity = $cityContext['launchingSoonCityName'] ?? $homeCity;
    $siteName = \App\Models\Setting::get('website_name', 'ApnaNest');
    $text = fn (string $key, string $fallback = '') => \App\Models\Setting::get($key, $fallback);
    $heroImage = \App\Models\City::resolveHeroImage($homeCity ?? $displayCity ?? null);
    $heroDescription = $text('home_hero_description', 'Discover verified rooms, PGs and flats for rent. Connect directly with genuine property owners.');
@endphp

@section('title', ($homeCity ? 'Verified Rooms & PG in '.$homeCity : 'Verified Rooms, PG & Apartments') . ' | ' . $siteName)
@section('description', $homeCity ? 'Find verified rooms, PG and apartments in '.$homeCity.'. Connect directly with property owners.' : 'Find verified rooms, PG and apartments. Compare rentals and connect directly with property owners.')
@section('canonical', route('home'))

@push('styles')
@include('partials.listings-ld')
<link rel="preload" href="{{ $heroImage }}" as="image" fetchpriority="high">
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
<main class="market-home">
    @include('home.partials.hero')

    @include('home.partials.categories')
    @include('home.partials.why-choose-us')
    @include('home.partials.latest-rooms')
    @include('home.partials.list-property')
    @include('home.partials.how-it-works')
    @include('home.partials.testimonials')

    @include('home.partials.editorial')
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchForm = document.querySelector('.market-search');
    if (!searchForm) return;

    const closeDropdowns = (except = null) => {
        searchForm.querySelectorAll('.market-custom-select.is-open').forEach(dropdown => {
            if (dropdown === except) return;
            dropdown.classList.remove('is-open');
            dropdown.closest('.market-field')?.classList.remove('has-open-dropdown');
            dropdown.querySelector('.market-custom-trigger')?.setAttribute('aria-expanded', 'false');
        });
    };

    searchForm.querySelectorAll('.market-field select').forEach((select, selectIndex) => {
        select.classList.add('market-native-select');

        const dropdown = document.createElement('div');
        dropdown.className = 'market-custom-select';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'market-custom-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', `market-options-${selectIndex}`);

        const triggerText = document.createElement('span');
        triggerText.textContent = select.options[select.selectedIndex]?.text || 'Select';
        trigger.appendChild(triggerText);

        const menu = document.createElement('div');
        menu.id = `market-options-${selectIndex}`;
        menu.className = 'market-custom-menu';
        menu.setAttribute('role', 'listbox');

        Array.from(select.options).forEach(option => {
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = 'market-custom-option';
            optionButton.textContent = option.text;
            optionButton.dataset.value = option.value;
            optionButton.setAttribute('role', 'option');

            if (option.selected) {
                optionButton.classList.add('is-selected');
                optionButton.setAttribute('aria-selected', 'true');
            }

            optionButton.addEventListener('click', () => {
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                triggerText.textContent = option.text;
                menu.querySelectorAll('.market-custom-option').forEach(item => {
                    const selected = item === optionButton;
                    item.classList.toggle('is-selected', selected);
                    item.setAttribute('aria-selected', selected ? 'true' : 'false');
                });
                closeDropdowns();
                trigger.focus();
            });

            menu.appendChild(optionButton);
        });

        trigger.addEventListener('click', event => {
            event.stopPropagation();
            const willOpen = !dropdown.classList.contains('is-open');
            closeDropdowns(dropdown);
            dropdown.classList.toggle('is-open', willOpen);
            dropdown.closest('.market-field')?.classList.toggle('has-open-dropdown', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        trigger.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown' && !dropdown.classList.contains('is-open')) {
                event.preventDefault();
                trigger.click();
                menu.querySelector('.is-selected, .market-custom-option')?.focus();
            }
        });

        dropdown.append(trigger, menu);
        select.insertAdjacentElement('afterend', dropdown);
    });

    document.addEventListener('click', () => closeDropdowns());
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeDropdowns();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    document.documentElement.classList.add('market-motion-ready');
    const revealSections = document.querySelectorAll('.market-section');

    revealSections.forEach(section => {
        section.classList.add('market-reveal');
        section.querySelectorAll('.market-type, .market-room, .market-area, .market-step, .market-blog, .market-review, .market-feature, .market-testimonial')
            .forEach(item => item.classList.add('market-reveal-item'));
    });

    const revealObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            revealObserver.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -45px 0px' });

    revealSections.forEach(section => revealObserver.observe(section));
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('[data-testimonial-slider]');
    if (!slider) return;
    const dotsWrap = document.querySelector('[data-testimonial-dots]');
    const cards = Array.from(slider.querySelectorAll('.market-testimonial'));
    if (!dotsWrap || cards.length < 2) return;
    let isDragging = false;
    let startX = 0;
    let startScrollLeft = 0;

    const dots = cards.map((card, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'market-testimonial-dot';
        dot.setAttribute('aria-label', `Show testimonial ${index + 1}`);
        dot.addEventListener('click', () => {
            slider.scrollTo({ left: card.offsetLeft - slider.offsetLeft, behavior: 'smooth' });
        });
        dotsWrap.appendChild(dot);
        return dot;
    });

    const updateDots = () => {
        const activeIndex = cards.reduce((closestIndex, card, index) => {
            const currentDistance = Math.abs(card.offsetLeft - slider.offsetLeft - slider.scrollLeft);
            const closestDistance = Math.abs(cards[closestIndex].offsetLeft - slider.offsetLeft - slider.scrollLeft);
            return currentDistance < closestDistance ? index : closestIndex;
        }, 0);

        dots.forEach((dot, index) => {
            dot.classList.toggle('is-active', index === activeIndex);
            dot.setAttribute('aria-current', index === activeIndex ? 'true' : 'false');
        });
    };

    slider.addEventListener('scroll', () => window.requestAnimationFrame(updateDots), { passive: true });
    slider.addEventListener('wheel', event => {
        if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) return;
        event.preventDefault();
        slider.scrollBy({ left: event.deltaY, behavior: 'smooth' });
    }, { passive: false });
    slider.addEventListener('pointerdown', event => {
        isDragging = true;
        startX = event.clientX;
        startScrollLeft = slider.scrollLeft;
        slider.classList.add('is-dragging');
        slider.setPointerCapture(event.pointerId);
    });
    slider.addEventListener('pointermove', event => {
        if (!isDragging) return;
        slider.scrollLeft = startScrollLeft - (event.clientX - startX);
    });
    const stopDrag = event => {
        if (!isDragging) return;
        isDragging = false;
        slider.classList.remove('is-dragging');
        if (slider.hasPointerCapture(event.pointerId)) {
            slider.releasePointerCapture(event.pointerId);
        }
    };
    slider.addEventListener('pointerup', stopDrag);
    slider.addEventListener('pointercancel', stopDrag);
    slider.addEventListener('pointerleave', stopDrag);
    window.addEventListener('resize', updateDots);
    updateDots();
});
</script>
@endpush
