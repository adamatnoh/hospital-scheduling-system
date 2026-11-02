@props(['value'])

<label {{ $attributes->merge(['class' => 'hss-label']) }}>
    {{ $value ?? $slot }}
</label>
