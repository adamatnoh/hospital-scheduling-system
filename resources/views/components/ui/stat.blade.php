@props(['label', 'value', 'icon' => 'bi-activity', 'tone' => 'brand', 'hint' => null, 'trend' => null, 'href' => null])
@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-100 dark:bg-brand-900/40 dark:text-brand-300 dark:ring-brand-800',
        'navy' => 'bg-navy-50 text-navy-700 ring-navy-100 dark:bg-navy-800/60 dark:text-navy-200 dark:ring-navy-700',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-900/30 dark:text-rose-300 dark:ring-rose-800',
        'evening' => 'bg-orange-50 text-shift-evening ring-orange-100 dark:bg-orange-900/30 dark:text-orange-300 dark:ring-orange-800',
        'night' => 'bg-violet-50 text-shift-night ring-violet-100 dark:bg-violet-900/30 dark:text-violet-300 dark:ring-violet-800',
    ];
    $tag = $href ? 'a' : 'article';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'hss-card group relative flex flex-col gap-3 transition hover:-translate-y-0.5 hover:shadow-[var(--shadow-raised)]']) }}>
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-muted">{{ $label }}</p>
        <span class="grid h-10 w-10 place-items-center rounded-xl text-lg ring-1 {{ $tones[$tone] ?? $tones['brand'] }}" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
    </div>
    <p class="text-3xl font-bold tracking-tight text-ink">{{ $value }}</p>
    <div class="flex flex-wrap items-center gap-2 text-xs text-muted">
        @if ($trend !== null)
            @php($up = $trend >= 0)
            <span class="hss-badge {{ $up ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' }}">
                <i class="bi {{ $up ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}" aria-hidden="true"></i>{{ $up ? '+' : '' }}{{ $trend }}%
            </span>
        @endif
        @if ($hint)<span>{{ $hint }}</span>@endif
        {{ $slot }}
    </div>
</{{ $tag }}>
