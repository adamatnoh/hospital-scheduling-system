@extends('layouts.hss')
@section('heading', $type->label().' application')
@section('content')
    <form method="POST" action="{{ $type->value === 'leave' ? route('calendar.storeLeave') : route('calendar.storeOnCall') }}" class="hss-card max-w-xl space-y-4">
        @csrf
        <input type="hidden" name="type" value="{{ $type->value }}">
        <div>
            <label for="start_date" class="text-sm font-semibold">Start date</label>
            <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required class="hss-input mt-1" aria-invalid="{{ $errors->has('start_date') ? 'true' : 'false' }}">
            @error('start_date') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="end_date" class="text-sm font-semibold">End date</label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required class="hss-input mt-1">
            @error('end_date') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="reason" class="text-sm font-semibold">Reason</label>
            <textarea id="reason" name="reason" required rows="4" class="hss-input mt-1">{{ old('reason') }}</textarea>
            @error('reason') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <button class="hss-btn" type="submit">Submit for review</button>
    </form>
@endsection
