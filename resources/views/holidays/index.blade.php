@extends('layouts.hss')
@section('heading', 'Public Holidays')
@section('content')
    <x-ui.page-header title="Public holidays" icon="bi-calendar-heart" subtitle="The generator spreads holiday duties fairly and flags them in the fairness report." />
    <div class="grid gap-6 lg:grid-cols-[22rem_1fr]">
        <x-ui.card title="Add a holiday" icon="bi-plus-lg" class="h-fit">
            <form method="POST" action="{{ route('holidays.store') }}" class="space-y-4">
                @csrf
                <x-ui.field label="Date" for="date" required><input id="date" name="date" type="date" required class="hss-input" value="{{ old('date') }}"></x-ui.field>
                <x-ui.field label="Name" for="name" required><input id="name" name="name" required class="hss-input" value="{{ old('name') }}" placeholder="e.g. Hari Raya Aidilfitri"></x-ui.field>
                <x-ui.field label="State" for="state">
                    <select id="state" name="state" class="hss-input">
                        @foreach ($states as $state)
                            <option value="{{ $state }}" @selected(old('state') === $state)>{{ $state }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.button class="w-full" icon="bi-check-lg">Save holiday</x-ui.button>
            </form>
        </x-ui.card>
        <x-ui.card flush title="Holiday calendar" icon="bi-calendar3" :subtitle="$holidays->count().' dates'">
            @if ($holidays->isEmpty())
                <x-ui.empty icon="bi-calendar-x" title="No holidays yet">Add the national and state holidays for this year.</x-ui.empty>
            @else
                <x-ui.table caption="Public holidays">
                    <x-slot:head><th>Date</th><th>Name</th><th>State</th><th class="text-right"><span class="sr-only">Actions</span></th></x-slot:head>
                    @foreach ($holidays as $holiday)
                        <tr class="{{ $holiday->date->isPast() ? 'opacity-60' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-subtle text-center leading-none ring-1 ring-line">
                                        <span><span class="block text-[0.6rem] font-semibold uppercase text-shift-evening">{{ $holiday->date->format('M') }}</span><span class="block text-sm font-bold text-ink">{{ $holiday->date->format('d') }}</span></span>
                                    </span>
                                    <span class="text-xs text-muted">{{ $holiday->date->format('l, Y') }}</span>
                                </div>
                            </td>
                            <td class="font-medium text-ink">{{ $holiday->name }}</td>
                            <td><x-ui.badge :tone="$holiday->state === 'National' ? 'brand' : 'slate'">{{ $holiday->state }}</x-ui.badge></td>
                            <td class="text-right">
                                <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" data-confirm="Remove {{ $holiday->name }}?" data-confirm-text="Remove" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" size="sm" icon="bi-trash3" class="!text-rose-700 dark:!text-rose-300">Remove</x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>
@endsection
