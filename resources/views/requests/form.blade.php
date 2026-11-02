@extends('layouts.hss')
@section('heading', $type->label().' application')
@section('content')
    <x-ui.page-header :title="$type->label().' application'" :icon="$type->value === 'leave' ? 'bi-airplane' : 'bi-moon-stars'"
        :subtitle="$type->value === 'leave' ? 'Ask for days off. The scheduler reviews it before the next roster.' : 'Ask for on-call days you would like to take.'" />
    <div class="grid gap-6 lg:grid-cols-[minmax(0,36rem)_1fr]">
        <x-ui.card>
            <form method="POST" action="{{ $type->value === 'leave' ? route('calendar.storeLeave') : route('calendar.storeOnCall') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $type->value }}">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Start date" for="start_date" required>
                        <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required class="hss-input" aria-invalid="{{ $errors->has('start_date') ? 'true' : 'false' }}">
                    </x-ui.field>
                    <x-ui.field label="End date" for="end_date" required>
                        <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required class="hss-input" aria-invalid="{{ $errors->has('end_date') ? 'true' : 'false' }}">
                    </x-ui.field>
                </div>
                <x-ui.field label="Reason" for="reason" required hint="A short note helps the reviewer decide quickly.">
                    <textarea id="reason" name="reason" required rows="4" class="hss-input" aria-invalid="{{ $errors->has('reason') ? 'true' : 'false' }}">{{ old('reason') }}</textarea>
                </x-ui.field>
                <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
                    <x-ui.button icon="bi-send">Submit for review</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('calendar.history')">View my history</x-ui.button>
                </div>
            </form>
        </x-ui.card>
        <x-ui.card title="How it works" icon="bi-info-circle" class="h-fit">
            <ol class="space-y-3 text-sm text-muted">
                <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">1</span>Send the dates and a reason.</li>
                <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">2</span>Your scheduler approves or rejects it with a note.</li>
                <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-50 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">3</span>You get a notification, and the next roster respects it.</li>
            </ol>
        </x-ui.card>
    </div>
@endsection
