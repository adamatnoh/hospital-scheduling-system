@extends('layouts.hss')
@section('heading', 'Your History')
@section('content')
    <x-ui.page-header title="My requests" icon="bi-clock-history" subtitle="Every leave, on-call and swap request you have sent, and what happened to it." />
    <x-ui.card flush>
        @if ($applications->isEmpty())
            <x-ui.empty icon="bi-inbox" title="No applications yet">Requests you send appear here with their status.</x-ui.empty>
        @else
            <x-ui.table caption="Your applications">
                <x-slot:head><th>Type</th><th>Dates</th><th>Reason</th><th>Status</th><th>Reviewer note</th></x-slot:head>
                @foreach ($applications as $application)
                    <tr>
                        <td class="font-medium text-ink">{{ $application->type->label() }}</td>
                        <td class="whitespace-nowrap tabular-nums">{{ $application->start_date->format('d M') }} – {{ $application->end_date->format('d M Y') }}</td>
                        <td class="max-w-xs">{{ $application->reason }}</td>
                        <td><x-ui.request-status :status="$application->status" /></td>
                        <td class="max-w-xs text-muted">{{ $application->rejection_reason ?: '—' }}</td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
@endsection
