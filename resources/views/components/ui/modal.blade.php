@props(['name', 'title' => null, 'maxWidth' => 'max-w-lg'])
<div x-data="{ show: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') { show = true; $nextTick(() => $refs.panel.querySelector('input,select,textarea,button')?.focus()) }"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') show = false"
     x-on:keydown.escape.window="show = false"
     x-show="show" x-cloak
     class="fixed inset-0 z-[60] flex items-end justify-center p-4 sm:items-center"
     role="dialog" aria-modal="true" aria-labelledby="modal-{{ $name }}-title">
    <div class="absolute inset-0 bg-navy-950/50 backdrop-blur-sm" x-show="show" x-transition.opacity @click="show = false"></div>
    <div x-ref="panel" x-show="show" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         class="relative w-full {{ $maxWidth }} rounded-2xl border border-line bg-surface shadow-2xl">
        <div class="flex items-center justify-between border-b border-line px-5 py-4">
            <h3 id="modal-{{ $name }}-title" class="font-semibold text-ink">{{ $title }}</h3>
            <button type="button" class="grid h-8 w-8 place-items-center rounded-lg text-muted hover:bg-subtle" @click="show = false" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="px-5 py-4">{{ $slot }}</div>
    </div>
</div>
