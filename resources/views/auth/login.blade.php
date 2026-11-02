<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HSS | Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-screen">
    <div class="auth-card">
        <img src="{{ asset('images/dr.jpg') }}" alt="" class="mx-auto mb-3 h-20 w-20 rounded-full object-cover">
        <h1 class="text-2xl font-semibold text-[#012970]">HSS | Login</h1>
        <p class="text-sm text-slate-500">Welcome onboard with us!</p>
        <hr class="my-3">
        @if (session('status'))
            <p class="mb-3 text-sm text-emerald-700" role="status">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('login') }}" class="space-y-3 text-left">
            @csrf
            <div>
                <label for="email" class="text-sm font-semibold">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="hss-input mt-1" placeholder="Enter your email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="text-sm font-semibold">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="hss-input mt-1" placeholder="Enter your password">
                @error('password') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input id="remember_me" type="checkbox" name="remember"> Remember me
            </label>
            <button class="hss-btn hss-btn-teal w-full" type="submit">Log in</button>
            @if (Route::has('password.request'))
                <a class="block text-center text-sm text-slate-600 underline" href="{{ route('password.request') }}">Forgot your password?</a>
            @endif
        </form>
        @if (app()->environment('local'))
            <div class="mt-4 rounded bg-slate-50 p-3 text-left text-xs text-slate-600">
                <p class="font-semibold text-[#012970]">Demo logins (password: password)</p>
                <p>admin@hss.test · scheduler@hss.test · doctor@hss.test</p>
            </div>
        @endif
    </div>
</body>
</html>
