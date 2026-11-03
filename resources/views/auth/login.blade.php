<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HSS | Login</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-canvas font-sans text-ink antialiased">
    <div class="grid min-h-full lg:grid-cols-[1.1fr_1fr]">
        {{-- Art panel: login_bg.jpg under a clinical gradient. Photo credits are in the README. --}}
        <aside class="login-art relative hidden flex-col justify-between overflow-hidden p-10 text-white lg:flex xl:p-14">
            <div class="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-brand-400/25 blur-3xl" aria-hidden="true"></div>
            <a href="{{ route('landing') }}" class="relative"><x-ui.logo dark size="lg" /></a>

            <div class="relative max-w-lg">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-200">{{ config('hss.hospital') }}</p>
                <h2 class="mt-4 text-4xl font-bold leading-tight tracking-tight xl:text-5xl">Rosters that keep every ward covered.</h2>
                <p class="mt-4 text-lg text-navy-100">On-call, ward and shift schedules for Orthopedics and Emergency, with leave, on-call and swap requests reviewed in one place.</p>
                <ul class="mt-8 grid gap-3 text-sm text-navy-50">
                    <li class="flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 ring-1 ring-white/15"><i class="bi bi-magic" aria-hidden="true"></i></span>Generate a fair month in seconds, preview before publishing</li>
                    <li class="flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 ring-1 ring-white/15"><i class="bi bi-shield-check" aria-hidden="true"></i></span>Coverage gaps flagged before they reach the ward</li>
                    <li class="flex items-center gap-3"><span class="grid h-8 w-8 place-items-center rounded-lg bg-white/10 ring-1 ring-white/15"><i class="bi bi-bell" aria-hidden="true"></i></span>Day-before on-call reminders by email</li>
                </ul>
            </div>

            <figure class="relative flex max-w-md items-center gap-4 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                <img src="{{ asset('images/doctor-avatar.webp') }}" width="192" height="192" alt="" class="h-14 w-14 rounded-xl object-cover ring-2 ring-white/30">
                <figcaption>
                    <p class="font-semibold">Welcome onboard with us!</p>
                    <p class="text-sm text-navy-100">Sign in with your hospital account to see your duties.</p>
                </figcaption>
            </figure>
        </aside>

        {{-- Form --}}
        <main class="flex items-center justify-center px-5 py-10 sm:px-10">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <div class="relative mb-6 h-36 overflow-hidden rounded-2xl login-art">
                        <img src="{{ asset('images/doctor-avatar.webp') }}" width="192" height="192" alt="" class="absolute bottom-3 right-3 h-16 w-16 rounded-xl object-cover ring-2 ring-white/40">
                        <div class="absolute left-4 top-4"><x-ui.logo dark /></div>
                    </div>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Sign in to HSS</h1>
                <p class="mt-1 text-sm text-muted">Hospital Scheduling System · {{ config('hss.hospital') }}</p>

                @if (session('status'))
                    <p class="mt-5 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900" role="status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i>{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5" x-data>
                    @csrf
                    <x-ui.field label="Email" for="email">
                        <div class="relative">
                            <i class="bi bi-envelope pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted" aria-hidden="true"></i>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   class="hss-input !py-2.5 pl-10" placeholder="you@hospital.gov.my" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @error('email') aria-describedby="email-error" @enderror>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Password" for="password">
                        <div class="relative">
                            <i class="bi bi-lock pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted" aria-hidden="true"></i>
                            <input id="password" name="password" type="password" required autocomplete="current-password" class="hss-input !py-2.5 pl-10 pr-11" placeholder="Enter your password">
                            <button type="button" class="absolute right-1.5 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-lg text-muted hover:bg-subtle hover:text-ink"
                                    aria-label="Show password" aria-controls="password"
                                    onclick="const i = document.getElementById('password'); const show = i.type === 'password'; i.type = show ? 'text' : 'password'; this.setAttribute('aria-label', show ? 'Hide password' : 'Show password'); this.firstElementChild.className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye');">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </x-ui.field>
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-muted">
                            <input id="remember_me" type="checkbox" name="remember" class="hss-check"> Remember me
                        </label>
                        @if (Route::has('password.request'))
                            <a class="font-semibold text-brand-700 hover:underline dark:text-brand-300" href="{{ route('password.request') }}">Forgot your password?</a>
                        @endif
                    </div>
                    <button class="hss-btn hss-btn-primary w-full !py-3 text-base" type="submit">Log in <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </form>

                @if (config('hss.demo_login'))
                    <section class="mt-8 rounded-2xl border border-brand-200 bg-brand-50/60 p-4 dark:border-brand-800 dark:bg-brand-900/20" aria-labelledby="demo-heading">
                        <div class="flex items-center justify-between gap-2">
                            <h2 id="demo-heading" class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-brand-800 dark:text-brand-200"><i class="bi bi-person-badge" aria-hidden="true"></i>Demo accounts</h2>
                            <span class="text-xs text-muted">One click, no password</span>
                        </div>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            @foreach ([
                                'admin' => ['Admin', 'bi-shield-lock', 'Staff, holidays, audit'],
                                'scheduler' => ['Scheduler', 'bi-calendar2-week', 'Generate and review'],
                                'doctor' => ['Doctor', 'bi-heart-pulse', 'Own duties and requests'],
                            ] as $demoRole => [$demoLabel, $demoIcon, $demoHint])
                                <form method="POST" action="{{ route('demo-login', $demoRole) }}">
                                    @csrf
                                    <button type="submit" data-demo-login="{{ $demoRole }}"
                                            class="group flex h-full w-full flex-col rounded-xl border border-line bg-surface px-3 py-2.5 text-left text-xs shadow-sm transition hover:border-brand-400 hover:bg-white focus-visible:outline-2 focus-visible:outline-brand-500 dark:hover:bg-brand-900/30">
                                        <span class="flex items-center gap-1.5 text-sm font-semibold text-ink"><i class="bi {{ $demoIcon }} text-brand-600 dark:text-brand-300" aria-hidden="true"></i>{{ $demoLabel }}</span>
                                        <span class="mt-0.5 block text-muted">{{ $demoHint }}</span>
                                        <span class="mt-1.5 inline-flex items-center gap-1 font-semibold text-brand-700 group-hover:underline dark:text-brand-300">Sign in <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </section>
                @endif

                <p class="mt-10 text-center text-xs text-muted">© {{ now()->year }} {{ config('hss.hospital') }}</p>
            </div>
        </main>
    </div>
</body>
</html>
