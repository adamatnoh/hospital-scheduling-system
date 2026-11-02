@extends('layouts.hss')
@section('heading', 'Your Schedule')
@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        @include('partials.legend')
        <a class="hss-btn" href="{{ route('ical.download') }}">Download iCal</a>
    </div>
    @if ($user->department_id)
        <div id="hss-calendar" class="hss-card" data-editable="0"
            data-events-url="{{ route('calendar.events', ['type' => 'all', 'department_id' => $user->department_id, 'mine' => 1]) }}"
            data-resources-url="{{ route('calendar.resources', ['department_id' => $user->department_id]) }}"></div>
        <p class="mt-3 text-sm text-slate-500">Published on-call, ward and shift duties for your login. Dragging is left to the scheduler.</p>
    @endif
@endsection
