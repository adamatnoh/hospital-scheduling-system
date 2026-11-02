@extends('layouts.hss')
@section('heading', 'Changes Requested History')
@section('content')
    <div class="hss-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b text-[#012970]"><th class="py-2">Doctor</th><th>Type</th><th>Dates</th><th>Status</th><th>Reason</th></tr></thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr class="border-b">
                        <td class="py-2">Dr {{ $application->user->name }}</td>
                        <td>{{ $application->type->label() }}</td>
                        <td>{{ $application->start_date->format('d M') }} – {{ $application->end_date->format('d M') }}</td>
                        <td>{{ $application->status->label() }}</td>
                        <td>{{ $application->rejection_reason ?: $application->reason }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-3">No decisions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
