{{-- Flash messages as toasts. Other scripts can call window.hssToast('Saved', 'success'). --}}
<div x-data="{
        toasts: [],
        add(message, tone = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, tone });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id) },
        init() {
            window.hssToast = (m, t) => this.add(m, t);
            @if (session('status')) this.add(@js(session('status')), 'success'); @endif
            @if (session('error')) this.add(@js(session('error')), 'error'); @endif
        },
     }"
     class="pointer-events-none fixed inset-x-0 top-4 z-[70] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:right-4 sm:items-end"
     aria-live="polite" role="status">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border border-line bg-surface p-4 shadow-[var(--shadow-raised)]">
            <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full"
                  :class="toast.tone === 'error' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'" aria-hidden="true">
                <i class="bi" :class="toast.tone === 'error' ? 'bi-exclamation-lg' : 'bi-check-lg'"></i>
            </span>
            <p class="flex-1 text-sm font-medium text-ink" x-text="toast.message"></p>
            <button type="button" class="text-muted hover:text-ink" @click="remove(toast.id)" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>
        </div>
    </template>
</div>
@if (session('status'))
    <noscript><p class="m-4 rounded-lg bg-emerald-50 p-3 text-emerald-900">{{ session('status') }}</p></noscript>
@endif
