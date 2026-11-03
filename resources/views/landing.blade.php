<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HSS | Hospital Scheduling System</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-canvas font-sans text-ink antialiased">
    <header class="sticky top-0 z-30 border-b border-line bg-surface/80 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-3">
            <a href="{{ route('landing') }}"><x-ui.logo /></a>
            <nav class="flex items-center gap-2 text-sm" aria-label="Main">
                <a href="#announcements" class="hidden rounded-lg px-3 py-2 font-medium text-muted hover:text-ink sm:block">Announcements</a>
                <a href="#features" class="hidden rounded-lg px-3 py-2 font-medium text-muted hover:text-ink sm:block">Features</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hss-btn hss-btn-primary">Open dashboard <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @else
                    <a href="{{ route('login') }}" class="hss-btn hss-btn-primary">Log in <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="relative overflow-hidden bg-gradient-to-br from-navy-950 via-navy-900 to-brand-800 text-white">
            <div class="pointer-events-none absolute inset-0 opacity-20" style="background: url('{{ asset('images/login_bg.jpg') }}') center / cover;" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-24 top-10 h-96 w-96 rounded-full bg-brand-400/25 blur-3xl" aria-hidden="true"></div>
            <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-5 py-16 md:grid-cols-[1.1fr_1fr] md:py-24">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand-100 ring-1 ring-white/15">
                        <i class="bi bi-hospital" aria-hidden="true"></i>{{ config('hss.hospital') }}
                    </p>
                    <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight md:text-5xl">Hospital Scheduling System</h1>
                    <h2 class="mt-3 text-xl font-medium text-brand-100">Doctor rosters for on-call, wards and shifts.</h2>
                    <p class="mt-4 max-w-xl text-navy-100">Built as a UTM final-year project in 2023 and rebuilt in 2026. Schedulers generate the month, doctors apply for leave, on-call days and swaps, and a reviewer approves or rejects with a reason.</p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="hss-btn bg-white !px-5 !py-3 !text-navy-900 hover:!bg-brand-50">{{ auth()->check() ? 'Open dashboard' : 'Sign in' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                        <a href="#announcements" class="hss-btn border-white/20 bg-white/10 !px-5 !py-3 hover:bg-white/20"><i class="bi bi-megaphone" aria-hidden="true"></i>Latest announcements</a>
                    </div>
                </div>
                <div class="relative mx-auto w-full max-w-sm">
                    <img src="{{ asset('images/doctor-1200.webp') }}"
                         srcset="{{ asset('images/doctor-600.webp') }} 600w, {{ asset('images/doctor-1200.webp') }} 1200w"
                         sizes="(min-width: 640px) 384px, 100vw" width="1200" height="1500"
                         alt="A doctor at the hospital" class="aspect-[4/5] w-full rounded-3xl object-cover shadow-2xl ring-1 ring-white/20">
                    <div class="absolute -bottom-6 -left-6 flex items-center gap-3 rounded-2xl bg-white p-4 text-navy-900 shadow-xl">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-50 text-brand-700"><i class="bi bi-calendar2-check" aria-hidden="true"></i></span>
                        <div>
                            <p class="text-sm font-bold">Roster published</p>
                            <p class="text-xs text-slate-500">Morning · Evening · Night</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="announcements" class="mx-auto max-w-6xl scroll-mt-20 px-5 py-14">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">Announcements</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight md:text-3xl">News from around the hospital</h2>
                </div>
            </div>
            <x-announcement-carousel :announcements="$announcements" variant="hero" :interval="7000" />
        </section>

        <section id="features" class="mx-auto max-w-6xl scroll-mt-20 px-5 pb-16">
            <div class="grid gap-5 md:grid-cols-3">
                <article class="hss-card">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-navy-50 text-xl text-navy-700 dark:bg-navy-800 dark:text-navy-200"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                    <h3 class="mt-4 font-semibold">Admin, scheduler and doctor</h3>
                    <p class="mt-1 text-sm text-muted">Each role sees only what it needs, from staff management down to a doctor's own duties.</p>
                </article>
                <article class="hss-card">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-xl text-brand-700 dark:bg-brand-900/40 dark:text-brand-300"><i class="bi bi-building" aria-hidden="true"></i></span>
                    <h3 class="mt-4 font-semibold">Departments kept apart</h3>
                    <p class="mt-1 text-sm text-muted">Orthopedics and Emergency each have their own rules, rosters and review queue.</p>
                </article>
                <article class="hss-card">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-violet-50 text-xl text-shift-night dark:bg-violet-900/30 dark:text-violet-200"><i class="bi bi-calendar3" aria-hidden="true"></i></span>
                    <h3 class="mt-4 font-semibold">The original shift colours</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-ui.badge tone="morning">Morning #8da399</x-ui.badge>
                        <x-ui.badge tone="evening">Evening #d65302</x-ui.badge>
                        <x-ui.badge tone="night">Night #311F62</x-ui.badge>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <footer class="border-t border-line">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-5 py-6 text-sm text-muted">
            <x-ui.logo size="sm" />
            <p>© {{ now()->year }} {{ config('hss.hospital') }}</p>
        </div>
    </footer>
</body>
</html>
