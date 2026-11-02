<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HSS | Hospital Scheduling System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    <header class="mx-auto flex max-w-5xl items-center justify-between px-4 py-5">
        <p class="text-xl font-bold text-[#012970]">HSS <span class="font-normal text-slate-500">| Hospital Scheduling System</span></p>
        <a href="{{ route('login') }}" class="hss-btn">Log in</a>
    </header>
    <main class="mx-auto grid max-w-5xl gap-8 px-4 py-8 md:grid-cols-2">
        <div>
            <p class="text-sm uppercase tracking-wide text-slate-500">{{ config('hss.hospital') }}</p>
            <h1 class="mt-2 text-4xl font-semibold text-[#012970]">Doctor rosters for on-call, wards and shifts.</h1>
            <p class="mt-4 text-slate-600">Built as a UTM final-year project in 2023 and rebuilt in 2026. Schedulers still generate the month, doctors still apply, and a reviewer still approves or rejects with a reason.</p>
            <ul class="mt-6 space-y-2 text-sm">
                <li><i class="bi bi-person-badge"></i> Admin, scheduler and doctor</li>
                <li><i class="bi bi-building"></i> Orthopedics and Emergency, kept apart</li>
                <li><i class="bi bi-calendar3"></i> Morning #8da399, Evening #d65302, Night #311F62</li>
            </ul>
        </div>
        <img src="{{ asset('images/dr.jpg') }}" alt="A doctor at the hospital" class="h-72 w-full rounded-xl object-cover">
    </main>
</body>
</html>
