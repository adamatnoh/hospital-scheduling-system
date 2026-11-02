@extends('layouts.hss')
@section('heading', 'Audit Log')
@section('content')
    <x-ui.page-header title="Audit log" icon="bi-shield-check" subtitle="The latest 150 changes to rosters, requests, staff and announcements." />
    <x-ui.card flush>
        @if ($logs->isEmpty())
            <x-ui.empty icon="bi-shield" title="No audit entries yet" />
        @else
            <x-ui.table caption="Audit entries">
                <x-slot:head><th>When</th><th>Who</th><th>Action</th><th>Detail</th></x-slot:head>
                @foreach ($logs as $log)
                    @php
                        [$area] = explode('.', $log->action.'.');
                        $tone = match (true) {
                            str_contains($log->action, 'delete') || str_contains($log->action, 'reject') => 'rose',
                            str_contains($log->action, 'approve') || str_contains($log->action, 'publish') => 'emerald',
                            str_contains($log->action, 'create') || str_contains($log->action, 'generate') => 'brand',
                            default => 'slate',
                        };
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap tabular-nums text-muted" title="{{ $log->created_at?->timezone(config('app.timezone'))->toDayDateTimeString() }}">{{ $log->created_at?->timezone(config('app.timezone'))->format('d M, H:i') }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <x-ui.avatar :name="$log->user?->name ?? 'System'" size="sm" />
                                <span class="font-medium text-ink">{{ $log->user?->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td><x-ui.badge :tone="$tone"><code class="font-mono text-[0.7rem]">{{ $log->action }}</code></x-ui.badge></td>
                        <td class="max-w-md">
                            @if (! empty($log->properties))
                                <details class="group">
                                    <summary class="cursor-pointer truncate text-xs text-muted hover:text-ink">{{ \Illuminate\Support\Str::limit(json_encode($log->properties), 70) }}</summary>
                                    <pre class="mt-2 max-h-48 overflow-auto rounded-lg bg-subtle p-2 text-[0.7rem] ring-1 ring-line">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>
@endsection
