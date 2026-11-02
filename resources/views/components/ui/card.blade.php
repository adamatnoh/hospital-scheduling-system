@props(['title' => null, 'subtitle' => null, 'icon' => null, 'flush' => false])
<section {{ $attributes->merge(['class' => 'hss-card '.($flush ? '!p-0 overflow-hidden' : '')]) }}>
    @if ($title || isset($actions))
        <header class="flex flex-wrap items-start justify-between gap-3 {{ $flush ? 'px-5 pt-5 pb-4' : 'mb-4' }}">
            <div class="flex items-center gap-2.5">
                @if ($icon)
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-subtle text-brand-600 ring-1 ring-line dark:text-brand-300" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
                @endif
                <div>
                    @if ($title)<h3 class="text-[0.95rem] font-semibold text-ink">{{ $title }}</h3>@endif
                    @if ($subtitle)<p class="text-xs text-muted">{{ $subtitle }}</p>@endif
                </div>
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>
    @endif
    {{ $slot }}
    @isset($footer)
        <footer class="{{ $flush ? 'border-t border-line px-5 py-3' : 'mt-4 border-t border-line pt-3' }}">{{ $footer }}</footer>
    @endisset
</section>
