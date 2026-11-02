@props(['icon' => 'bi-inbox', 'title' => 'Nothing here yet'])
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-10 text-center']) }}>
    <span class="mb-3 grid h-14 w-14 place-items-center rounded-2xl bg-subtle text-2xl text-muted ring-1 ring-line" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
    <p class="font-semibold text-ink">{{ $title }}</p>
    @if (trim($slot))
        <div class="mt-1 max-w-sm text-sm text-muted">{{ $slot }}</div>
    @endif
</div>
