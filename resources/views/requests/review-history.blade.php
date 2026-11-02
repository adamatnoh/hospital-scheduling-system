@extends('layouts.hss')
@section('heading', 'Changes Requested History')
@section('content')
    <x-ui.page-header title="Decision history" icon="bi-journal-check" subtitle="Requests that have already been approved or rejected.">
        <x-slot:actions><x-ui.button variant="outline" icon="bi-inbox" :href="route('review')">Pending queue</x-ui.button></x-slot:actions>
    </x-ui.page-header>
    <x-ui.card flush>
        @if ($applications->isEmpty())
            <x-ui.empty icon="bi-journal" title="No decisions yet">Approved and rejected requests will be listed here.</x-ui.empty>
        @else
            <x-ui.table caption="Decided applications">
                <x-slot:head><th>Doctor</th><th>Type</th><th>Dates</th><th>Status</th><th>Reason / note</th></x-slot:head>
                @foreach ($applications as $application)
                    <tr>
                        <td><div class="flex items-center gap-3"><x-ui.avatar :name="$application->user->name" size="sm" /><span class="font-medium text-ink">Dr {{ $application->user->name }}</span></div></td>
                        <td>{{ $application->type->label() }}</td>
                        <td class="whitespace-nowrap tabular-nums">{{ $application->start_date->format('d M') }} – {{ $application->end_date->format('d M') }}</td>
                        <td><x-ui.request-status :status="$application->status" /></td>
                        <td class="max-w-sm text-muted">{{ $application->rejection_reason ?: $application->reason }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
@endsection
