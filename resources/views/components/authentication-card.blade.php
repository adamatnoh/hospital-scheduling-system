<div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-10">
    <div class="login-art pointer-events-none absolute inset-x-0 top-0 h-72" aria-hidden="true"></div>
    <div class="relative">{{ $logo }}</div>
    <div class="relative mt-6 w-full rounded-2xl border border-line bg-surface p-6 shadow-[var(--shadow-raised)] sm:max-w-md sm:p-8">
        {{ $slot }}
    </div>
    <p class="relative mt-6 text-xs text-muted">© {{ now()->year }} {{ config('hss.hospital') }}</p>
</div>
