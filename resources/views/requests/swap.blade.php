@extends('layouts.hss')
@section('heading', 'Request a Shift Swap')
@section('content')
    <form method="POST" action="{{ route('swaps.store') }}" class="hss-card max-w-xl space-y-4">
        @csrf
        <div>
            <label for="assignment_id" class="text-sm font-semibold">Your duty</label>
            <select id="assignment_id" name="assignment_id" required class="hss-input mt-1">
                <option value="">Choose</option>
                @foreach ($mine as $assignment)
                    <option value="{{ $assignment->id }}">{{ $assignment->date->format('d M') }} · {{ $assignment->type->label() }} {{ $assignment->shift?->value }} {{ $assignment->ward?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="counterpart_assignment_id" class="text-sm font-semibold">Colleague duty</label>
            <select id="counterpart_assignment_id" name="counterpart_assignment_id" required class="hss-input mt-1">
                <option value="">Choose</option>
                @foreach ($others as $assignment)
                    <option value="{{ $assignment->id }}">Dr {{ $assignment->user->name }} · {{ $assignment->date->format('d M') }} · {{ $assignment->type->label() }} {{ $assignment->shift?->value }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="reason" class="text-sm font-semibold">Reason</label>
            <textarea id="reason" name="reason" required class="hss-input mt-1" rows="3">{{ old('reason') }}</textarea>
        </div>
        <button class="hss-btn" type="submit">Send swap request</button>
    </form>
@endsection
