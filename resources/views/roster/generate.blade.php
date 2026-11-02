@extends('layouts.hss')
@section('heading', 'Create '.$type->label().' Schedule')
@section('content')
    <x-ui.page-header :title="'Generate '.strtolower($type->label()).' roster'" icon="bi-magic"
        subtitle="Build a preview, check fairness and gaps, adjust by dragging, then publish.">
        @if ($departments->isNotEmpty())
            <x-slot:actions>
                <x-ui.button variant="outline" icon="bi-sliders" :href="route('rules.show', $departmentId)">Department rules</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.card class="mb-6">
        <form method="POST" action="{{ route('generate.store', $departmentId) }}" class="grid items-end gap-4 sm:grid-cols-2 lg:grid-cols-[repeat(3,minmax(0,1fr))_auto]" data-confirm="Generate a new preview for this month?">
            @csrf
            <input type="hidden" name="type" value="{{ $type->value }}">
            @if (auth()->user()->isAdmin())
                <x-ui.field label="Department" for="department_id">
                    <select id="department_id" class="hss-input" onchange="window.location='{{ route('generate.show', $type->value) }}?department_id='+this.value+'&year={{ $year }}&month={{ $month }}'">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($department->id === $departmentId)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
            @endif
            <x-ui.field label="Month" for="month">
                <select id="month" name="month" class="hss-input" required>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" @selected((int) $month === $m)>{{ \Illuminate\Support\Carbon::create(2000, $m, 1)->format('F') }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Year" for="year">
                <input id="year" name="year" type="number" min="2023" max="2100" value="{{ $year }}" class="hss-input" required>
            </x-ui.field>
            <x-ui.button icon="bi-magic" class="h-[2.625rem]" :disabled="$departmentId === 0">Generate preview</x-ui.button>
        </form>
    </x-ui.card>

    @if ($run)
        @php($doctors = collect($run->fairness['doctors'] ?? []))
        @php($maxDuties = max(1, (int) $doctors->max('duties')))
        @php($gapCount = count($run->fairness['coverage_gaps'] ?? []))
        <div class="mb-6 grid gap-6 lg:grid-cols-3">
            <x-ui.card class="lg:col-span-2" title="Fairness report" icon="bi-bar-chart-steps" :subtitle="'Spread between the busiest and quietest doctor: '.($run->fairness['spread'] ?? 0).' duties'">
                <x-slot:actions>
                    <x-ui.badge :tone="$run->status->value === 'preview' ? 'amber' : 'emerald'" dot>{{ ucfirst($run->status->value) }}</x-ui.badge>
                </x-slot:actions>
                <ul class="grid gap-x-8 gap-y-3 sm:grid-cols-2">
                    @foreach ($doctors as $doctor)
                        <li>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-ink">Dr {{ $doctor['name'] }}</span>
                                <span class="tabular-nums text-muted">{{ $doctor['duties'] }}@if (($doctor['holiday_duties'] ?? 0) > 0) · <span class="text-shift-evening">{{ $doctor['holiday_duties'] }} holiday</span>@endif</span>
                            </div>
                            <div class="mt-1 h-2 rounded-full bg-subtle ring-1 ring-line"><div class="h-2 rounded-full bg-gradient-to-r from-brand-400 to-brand-600" style="width: {{ round($doctor['duties'] / $maxDuties * 100) }}%"></div></div>
                        </li>
                    @endforeach
                </ul>
                @if ($gapCount || ! empty($run->fairness['notes']))
                    <div class="mt-5 space-y-2 border-t border-line pt-4 text-sm">
                        @if ($gapCount)
                            <p class="flex items-center gap-2 font-medium text-shift-evening"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>{{ $gapCount }} coverage gap(s). The preview did not invent doctors to fill them.</p>
                        @endif
                        @foreach ($run->fairness['notes'] ?? [] as $note)
                            <p class="flex gap-2 text-muted"><i class="bi bi-info-circle" aria-hidden="true"></i>{{ $note }}</p>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
            <x-ui.card title="Publish" icon="bi-send-check">
                @if ($run->status->value === 'preview')
                    <p class="mb-4 text-sm text-muted">Publishing replaces this department's {{ $type->label() }} duties for {{ \Illuminate\Support\Carbon::create($year, $month, 1)->format('F Y') }} only. Doctors get it in their calendar straight away.</p>
                    <form method="POST" action="{{ route('runs.publish', $run) }}" data-confirm="Publish this roster for the department?">
                        @csrf
                        <x-ui.button class="w-full" icon="bi-send-check">Publish roster</x-ui.button>
                    </form>
                @else
                    <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-4 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-200">
                        <i class="bi bi-check-circle-fill text-xl" aria-hidden="true"></i>
                        <p class="text-sm font-semibold">Published {{ $run->published_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                    </div>
                @endif
                <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-subtle p-3 ring-1 ring-line"><dt class="text-xs text-muted">Doctors</dt><dd class="text-lg font-bold">{{ $doctors->count() }}</dd></div>
                    <div class="rounded-xl bg-subtle p-3 ring-1 ring-line"><dt class="text-xs text-muted">Gaps</dt><dd class="text-lg font-bold {{ $gapCount ? 'text-shift-evening' : '' }}">{{ $gapCount }}</dd></div>
                </dl>
            </x-ui.card>
        </div>
        <x-ui.card title="Preview" icon="bi-calendar3" subtitle="Drag a duty to another day to adjust before publishing">
            <div class="mb-4">@include('partials.legend')</div>
            <div id="hss-calendar" data-editable="1"
                data-events-url="{{ route('calendar.events', ['type' => $type->value, 'department_id' => $departmentId, 'run' => $run->id]) }}"
                data-resources-url="{{ route('calendar.resources', ['department_id' => $departmentId]) }}"></div>
        </x-ui.card>
    @else
        <x-ui.card><x-ui.empty icon="bi-calendar-plus" title="No preview for this month yet">Pick a month above and generate a preview. Nothing is published until you say so.</x-ui.empty></x-ui.card>
    @endif
@endsection
