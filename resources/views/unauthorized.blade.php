<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HSS | Unauthorized</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-slate-100">
    <div class="hss-card max-w-md text-center">
        <h1 class="text-2xl font-semibold text-[#012970]">That page is for another role</h1>
        <p class="mt-2 text-slate-600">Your account does not include this part of the roster.</p>
        <a class="hss-btn mt-4" href="{{ route('dashboard') }}">Back to the dashboard</a>
    </div>
</body>
</html>
