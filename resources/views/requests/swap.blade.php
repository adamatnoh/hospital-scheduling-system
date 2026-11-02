@extends('layouts.hss')
@section('heading', 'Request a Shift Swap')
@section('content')
    <x-ui.page-header title="Request a shift swap" icon="bi-arrow-left-right" subtitle="Trade one of your duties with a colleague. The scheduler confirms the swap." />
    <x-ui.card class="max-w-2xl">
        <form method="POST" action="{{ route('swaps.store') }}" class="space-y-5">
            @csrf
            <x-ui.field label="Your duty" for="assignment_id" required>
                <select id="assignment_id" name="assignment_id" required class="hss-input">
                    <option value="">Choose one of your duties</option>
                    @foreach ($mine as $assignment)
                        <option value="{{ $assignment->id }}" @selected(old('assignment_id') == $assignment->id)>{{ $assignment->date->format('D, d M') }} · {{ $assignment->type->label() }} {{ $assignment->shift?->value }} {{ $assignment->ward?->name }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <div class="flex justify-center text-muted" aria-hidden="true"><i class="bi bi-arrow-down-up"></i></div>
            <x-ui.field label="Colleague's duty" for="counterpart_assignment_id" required>
                <select id="counterpart_assignment_id" name="counterpart_assignment_id" required class="hss-input">
                    <option value="">Choose a colleague's duty</option>
                    @foreach ($others as $assignment)
                        <option value="{{ $assignment->id }}" @selected(old('counterpart_assignment_id') == $assignment->id)>Dr {{ $assignment->user->name }} · {{ $assignment->date->format('D, d M') }} · {{ $assignment->type->label() }} {{ $assignment->shift?->value }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Reason" for="reason" required>
                <textarea id="reason" name="reason" required class="hss-input" rows="3">{{ old('reason') }}</textarea>
            </x-ui.field>
            <div class="border-t border-line pt-5">
                <x-ui.button icon="bi-send">Send swap request</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
