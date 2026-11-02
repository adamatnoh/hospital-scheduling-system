<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('heading', 'HSS') | Hospital Scheduling System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="hss-shell" x-data="{ open: false }">
        @include('partials.sidebar')
        <div class="min-w-0">
            <header class="hss-top">
                <button type="button" class="rounded border px-2 py-1 md:hidden" @click="open = !open" aria-label="Open menu">
                    <i class="bi bi-list text-xl"></i>
                </button>
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">{{ config('hss.hospital') }}</p>
                    <h1 class="text-lg font-semibold text-[#012970]">@yield('heading', 'Dashboard')</h1>
                </div>
                <div class="text-sm text-right">
                    <p class="font-semibold">{{ auth()->user()->displayName() }}</p>
                    <p class="text-slate-500">{{ auth()->user()->role?->label() }}{{ auth()->user()->department ? ' · '.auth()->user()->department->name : '' }}</p>
                </div>
            </header>
            <main class="p-4 md:p-6">
                @if (session('status'))
                    <p class="mb-4 rounded border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-900" role="status">{{ session('status') }}</p>
                @endif
                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-200 bg-red-50 px-3 py-2 text-red-900" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>
