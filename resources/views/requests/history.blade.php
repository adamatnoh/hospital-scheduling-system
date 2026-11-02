@extends('layouts.hss')
@section('heading', 'Your History')
@section('content')
    <div class="hss-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b text-[#012970]">
                    <th class="py-2">Type</th><th>Dates</th><th>Reason</th><th>Status</th><th>Reviewer note</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($applications as $application)
                    <tr class="border-b">
                        <td class="py-2">{{ $application->type->label() }}</td>
                        <td>{{ $application->start_date->format('d M') }} – {{ $application->end_date->format('d M Y') }}</td>
                        <td>{{ $application->reason }}</td>
                        <td>{{ $application->status->label() }}</td>
                        <td>{{ $application->rejection_reason }}</td>
                    </tr>
                @empty
                    <tr><td class="py-3" colspan="5">No applications yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
