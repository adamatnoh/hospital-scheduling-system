<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('heading', 'HSS') | Hospital Scheduling System</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-canvas font-sans text-ink antialiased">
    <a href="#main" class="skip-link">Skip to content</a>
    <div x-data="{ open: false }" @keydown.escape.window="open = false" class="min-h-full">
        @include('partials.sidebar')

        <div class="lg:pl-72">
            @include('partials.topbar')

            <main id="main" class="mx-auto max-w-[1440px] px-4 py-6 sm:px-6 lg:px-8" tabindex="-1">
                @if ($errors->any())
                    <div class="mb-5 flex gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-900 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200" role="alert">
                        <i class="bi bi-exclamation-triangle-fill mt-0.5 text-rose-600" aria-hidden="true"></i>
                        <div>
                            <p class="font-semibold">Please fix the following:</p>
                            <ul class="mt-1 list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                @yield('content')
            </main>

            <footer class="mx-auto max-w-[1440px] px-4 pb-6 text-xs text-muted sm:px-6 lg:px-8">
                © {{ now()->year }} {{ config('hss.hospital') }} · Hospital Scheduling System
            </footer>
        </div>
    </div>
    <x-ui.toasts />
    @livewireScripts
    @stack('scripts')
</body>
</html>
