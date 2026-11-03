@props(['announcements', 'variant' => 'card', 'interval' => 6000])
{{--
    Announcement carousel. Revived from the 2023 FYP dashboard slideshow
    (resources/views/components/welcome.blade.php at tag v1-fyp), now data driven.
    Behaviour lives in resources/js/carousel.js: autoplay, pause on hover/focus,
    arrows, dots, swipe, keyboard, and no autoplay for reduced-motion users.
--}}
@php
    $hero = $variant === 'hero';
    $count = $announcements->count();
    $id = 'carousel-'.\Illuminate\Support\Str::random(6);
@endphp

@if ($count === 0)
    <section {{ $attributes->merge(['class' => 'hss-card']) }} aria-label="Announcements">
        <x-ui.empty icon="bi-megaphone" title="No announcements right now">
            @if (auth()->user()?->isAdmin())
                <a href="{{ route('announcements.create') }}" class="font-semibold text-brand-700 underline dark:text-brand-300">Add the first announcement</a>
            @else
                Hospital notices from the admin team will appear here.
            @endif
        </x-ui.empty>
    </section>
@else
<section id="{{ $id }}" data-carousel data-interval="{{ $interval }}"
         {{ $attributes->merge(['class' => 'hss-carousel group/carousel relative w-full min-w-0 overflow-hidden rounded-2xl border border-line bg-surface shadow-[var(--shadow-card)]']) }}
         aria-roledescription="carousel" aria-label="Hospital announcements">
    <div class="overflow-hidden" data-carousel-viewport>
        <div class="flex" data-carousel-track aria-live="off" id="{{ $id }}-track">
            @foreach ($announcements as $index => $announcement)
                <article class="grid w-full min-w-0 shrink-0 grow-0 basis-full grid-cols-1 {{ $hero ? 'md:grid-cols-[1.1fr_1fr]' : 'md:grid-cols-[1fr_1fr]' }}"
                         role="group" aria-roledescription="slide" aria-label="{{ $index + 1 }} of {{ $count }}: {{ $announcement->title }}"
                         data-carousel-slide @if ($index > 0) aria-hidden="true" @endif>
                    <div class="relative isolate flex min-w-0 items-center justify-center overflow-hidden bg-navy-950 {{ $hero ? 'h-72 md:h-[26rem]' : 'h-64 md:h-[21rem]' }}">
                        @if ($announcement->image_url)
                            <img src="{{ $announcement->image_url }}" alt="" aria-hidden="true" class="absolute inset-0 -z-10 h-full w-full scale-110 object-cover opacity-50 blur-2xl" loading="lazy">
                            <img src="{{ $announcement->image_url }}" alt="Poster: {{ $announcement->title }}"
                                 class="h-full w-auto min-w-0 max-w-full object-contain p-3 drop-shadow-2xl" @if ($index > 0) loading="lazy" @endif>
                        @else
                            <i class="bi bi-megaphone text-6xl text-white/40" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div class="flex min-w-0 flex-col justify-between gap-4 p-5 md:p-7">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.badge tone="brand" icon="bi-megaphone-fill">Announcement</x-ui.badge>
                                @if ($announcement->published_at)
                                    <span class="text-xs text-muted"><time datetime="{{ $announcement->published_at->toDateString() }}">{{ $announcement->published_at->format('d M Y') }}</time></span>
                                @endif
                            </div>
                            <h3 class="mt-3 font-bold tracking-tight break-words text-ink {{ $hero ? 'text-2xl md:text-3xl' : 'text-xl' }}">{{ $announcement->title }}</h3>
                            @if ($announcement->body)
                                <p class="mt-2 text-sm leading-relaxed break-words text-muted {{ $hero ? 'md:text-base' : 'line-clamp-5' }}">{{ $announcement->body }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($announcement->link_url)
                                <a href="{{ $announcement->link_url }}" class="hss-btn hss-btn-primary hss-btn-sm" target="_blank" rel="noopener" @if ($index > 0) tabindex="-1" @endif data-carousel-focusable>
                                    {{ $announcement->link_label ?: 'Read more' }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                                </a>
                            @endif
                            @if ($announcement->image_url)
                                <a href="{{ $announcement->image_url }}" class="hss-btn hss-btn-outline hss-btn-sm" target="_blank" rel="noopener" @if ($index > 0) tabindex="-1" @endif data-carousel-focusable>
                                    <i class="bi bi-arrows-fullscreen" aria-hidden="true"></i> View poster
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>

    @if ($count > 1)
        <div class="pointer-events-none absolute inset-x-0 top-0 flex {{ $hero ? 'h-72 md:h-[26rem]' : 'h-64 md:h-[21rem]' }} items-center justify-between px-3 md:w-1/2 {{ $hero ? 'md:w-[52.4%]' : '' }}">
            <button type="button" data-carousel-prev aria-controls="{{ $id }}-track" aria-label="Previous announcement"
                    class="pointer-events-auto grid h-10 w-10 place-items-center rounded-full bg-white/90 text-navy-900 shadow-lg ring-1 ring-black/5 transition hover:bg-white md:opacity-0 md:group-hover/carousel:opacity-100 md:focus-visible:opacity-100">
                <i class="bi bi-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button" data-carousel-next aria-controls="{{ $id }}-track" aria-label="Next announcement"
                    class="pointer-events-auto grid h-10 w-10 place-items-center rounded-full bg-white/90 text-navy-900 shadow-lg ring-1 ring-black/5 transition hover:bg-white md:opacity-0 md:group-hover/carousel:opacity-100 md:focus-visible:opacity-100">
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-line px-5 py-3">
            <div class="flex items-center gap-1.5" role="group" aria-label="Choose announcement">
                @foreach ($announcements as $index => $announcement)
                    <button type="button" class="hss-carousel-dot" data-carousel-dot="{{ $index }}" aria-controls="{{ $id }}-track"
                            aria-label="Show announcement {{ $index + 1 }}: {{ $announcement->title }}" @if ($index === 0) aria-current="true" @endif></button>
                @endforeach
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs tabular-nums text-muted" data-carousel-counter>1 / {{ $count }}</span>
                <button type="button" data-carousel-toggle class="grid h-8 w-8 place-items-center rounded-full text-muted ring-1 ring-line hover:bg-subtle hover:text-ink" aria-label="Pause announcements">
                    <i class="bi bi-pause-fill" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-0.5 bg-transparent" aria-hidden="true">
            <div class="hss-carousel-progress h-full bg-brand-500/70" data-carousel-progress style="transform: scaleX(0)"></div>
        </div>
    @endif
</section>
@endif
