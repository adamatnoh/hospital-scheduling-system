@props(['status'])
@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    [$tone, $icon] = match ($value) {
        'approved' => ['emerald', 'bi-check-circle-fill'],
        'rejected' => ['rose', 'bi-x-circle-fill'],
        default => ['amber', 'bi-hourglass-split'],
    };
@endphp
<x-ui.badge :tone="$tone" :icon="$icon" {{ $attributes }}>{{ $status instanceof \App\Enums\RequestStatus ? $status->label() : ucfirst($value) }}</x-ui.badge>
