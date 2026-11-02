<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HSS | Unauthorized</title>
    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-full place-items-center bg-canvas p-5 font-sans text-ink antialiased">
    <main class="hss-card w-full max-w-md !p-8 text-center">
        <x-ui.logo :text="false" class="justify-center" />
        <span class="mx-auto mt-6 grid h-14 w-14 place-items-center rounded-2xl bg-amber-50 text-2xl text-amber-700 ring-1 ring-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
        <h1 class="mt-4 text-2xl font-bold tracking-tight">That page is for another role</h1>
        <p class="mt-2 text-muted">Your account does not include this part of the roster. Ask an administrator if you think you should have access.</p>
        <div class="mt-6 flex justify-center gap-2">
            <a class="hss-btn hss-btn-primary" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>Back to the dashboard</a>
        </div>
    </main>
</body>
</html>
