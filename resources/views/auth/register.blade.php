<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-6 flex items-center gap-4">
            <img src="{{ asset('images/doctor-avatar.webp') }}" width="192" height="192" alt="" class="h-12 w-12 rounded-xl object-cover ring-1 ring-line">
            <div>
                <h1 class="text-xl font-bold tracking-tight">Register</h1>
                <p class="text-sm text-muted">Welcome onboard with us!</p>
            </div>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Your full name" />
            </div>
            <div>
                <x-label for="department" value="{{ __('Department') }}" />
                <select name="department" id="department" class="hss-input">
                    <optgroup label="{{ config('hss.hospital') }}">
                        <option value="Orthopedics" @selected(old('department') === 'Orthopedics')>Orthopedics</option>
                        <option value="Emergency" @selected(old('department') === 'Emergency')>Emergency</option>
                    </optgroup>
                </select>
            </div>
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@hospital.gov.my" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label for="password" value="{{ __('Password') }}" />
                    <x-input id="password" type="password" name="password" required autocomplete="new-password" />
                </div>
                <div>
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                    <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
            </div>


            <div class="flex items-center justify-between gap-3 pt-2">
                <a class="text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
                <x-button>{{ __('Register') }}</x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
