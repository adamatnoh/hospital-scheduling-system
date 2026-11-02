@props(['label', 'for', 'name' => null, 'hint' => null, 'required' => false])
@php($errorKey = $name ?? $for)
<div {{ $attributes }}>
    <label for="{{ $for }}" class="hss-label">{{ $label }}@if ($required)<span class="text-rose-600" aria-hidden="true"> *</span>@endif</label>
    {{ $slot }}
    @if ($hint)
        <p class="mt-1 text-xs text-muted" id="{{ $for }}-hint">{{ $hint }}</p>
    @endif
    @error($errorKey)
        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-rose-600" id="{{ $for }}-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</p>
    @enderror
</div>
