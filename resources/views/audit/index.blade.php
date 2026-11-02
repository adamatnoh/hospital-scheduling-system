@extends('layouts.hss')
@section('heading', 'Audit Log')
@section('content')
    <div class="hss-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b text-[#012970]"><th class="py-2">When</th><th>Who</th><th>Action</th><th>Detail</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b">
                        <td class="py-2">{{ $log->created_at?->timezone(config('app.timezone'))->format('d M H:i') }}</td>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td>{{ $log->action }}</td>
                        <td class="max-w-md truncate">{{ json_encode($log->properties) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-3">No audit entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
