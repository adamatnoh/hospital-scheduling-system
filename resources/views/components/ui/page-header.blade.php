@props(['title', 'subtitle' => null, 'icon' => null])
<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-end justify-between gap-4']) }}>
    <div class="flex items-center gap-3">
        @if ($icon)
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-xl text-brand-700 ring-1 ring-brand-100 dark:bg-brand-900/40 dark:text-brand-300 dark:ring-brand-800" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
        @endif
        <div>
            <h2 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-0.5 text-sm text-muted">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
