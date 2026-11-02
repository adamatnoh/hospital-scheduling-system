@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-200']) }} role="alert">
        <p class="flex items-center gap-2 font-semibold"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>{{ __('Please check the form.') }}</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
