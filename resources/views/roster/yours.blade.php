@extends('layouts.hss')
@section('heading', 'Your Schedule')
@section('content')
    <x-ui.page-header title="Your schedule" icon="bi-calendar-check"
        subtitle="Published on-call, ward and shift duties for your login.">
        <x-slot:actions>
            <x-ui.button variant="outline" icon="bi-calendar2-plus" :href="route('ical.download')">Download iCal</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>
    @if ($user->department_id)
        <x-ui.card>
            <div class="mb-4">@include('partials.legend')</div>
            <div id="hss-calendar" data-editable="0"
                data-events-url="{{ route('calendar.events', ['type' => 'all', 'department_id' => $user->department_id, 'mine' => 1]) }}"
                data-resources-url="{{ route('calendar.resources', ['department_id' => $user->department_id]) }}"></div>
        </x-ui.card>
    @else
        <x-ui.card><x-ui.empty icon="bi-calendar-x" title="No department on your account">Duties appear once you belong to a department. Administrators have no personal roster.</x-ui.empty></x-ui.card>
    @endif
@endsection
