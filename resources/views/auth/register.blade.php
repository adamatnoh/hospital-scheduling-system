<!DOCTYPE html>
<html>  
    <head>
        <title> HSS | Registration </title>
        <link rel="stylesheet" href="css/base_style.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"> 
    </head>
    <body>
        <div class="container h-100">
        <div class="d-flex justify-content-center h-100">
        <div class=auth-card>
            <x-slot name="logo">
                <x-authentication-card-logo />
            </x-slot>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <h2 style="text-align: center"> Register </h2>
                <p style="text-align: center; color: gray"> Welcome onboard with us! </p>
                <hr class="mb-3">

                <div>
                    <x-label for="name" class="label" value="{{ __('Name') }}" /><br>
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Enter your username" />
                </div>

                {{-- <div class="mt-4">
                    <x-label for="department" value="{{ __('Department') }}" /><br>
                    <x-input id="department" class="block mt-1 w-full" type="text" name="department" :value="old('department')" required autofocus autocomplete="department" placeholder="Enter your department" />
                </div> --}}
                <div class="col-span-6 sm:col-span-4 mt-4">
                    <x-label for="department" value="{{ __('Department') }}" /><br>
                    <select name="department" id="department" class="block w-full mt-1">
                        <optgroup label="Hospital Besar Hajjah Khalsom">
                            <option value="Orthopedics">Orthopedics</option>
                            <option value="Emergency">Emergency</option>
                        </optgroup>
                        {{-- <optgroup label="Quebec">
                           <option value="montreal">Montreal</option>
                           <option value="qc">Quebec City</option>
                        </optgroup> --}}
                    </select>
                </div>

                <div class="mt-4">
                    <x-label for="email" value="{{ __('Email') }}" /><br>
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Enter your email" />
                </div>

                <div class="mt-4">
                    <x-label for="password" value="{{ __('Password') }}" /><br>
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="Enter your password" />
                </div>

                <div class="mt-4">
                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" /><br>
                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter your password" />
                </div>

                <x-validation-errors class="mb-4" />

                @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                    <div class="mt-4">
                        <x-label for="terms">
                            <div class="flex items-center">
                                <x-checkbox name="terms" id="terms" required />

                                <div class="ml-2">
                                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                            'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                            'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                    ]) !!}
                                </div>
                            </div>
                        </x-label>
                    </div>
                @endif

                <div class="flex items-center justify-end mt-4">
                    <x-button class="ml-4 button" style="color:black">
                        {{ __('Register') }}
                    </x-button>
                    <br>
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a> 
                </div>
            </form>
        </div>
        </div>
        </div>
    </body>
</html>