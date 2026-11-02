@extends('layouts.hss')
@section('heading', 'Create '.$type->label().' Schedule')
@section('content')
    <form method="POST" action="{{ route('generate.store', $departmentId) }}" class="hss-card mb-4 grid gap-3 md:grid-cols-4" data-confirm="Generate a new preview for this month?">
        @csrf
        <input type="hidden" name="type" value="{{ $type->value }}">
        @if (auth()->user()->isAdmin())
            <div>
                <label for="department_id" class="text-sm font-semibold">Department</label>
                <select id="department_id" class="hss-input mt-1" onchange="window.location='{{ route('generate.show', $type->value) }}?department_id='+this.value+'&year={{ $year }}&month={{ $month }}'">
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected($department->id === $departmentId)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <label for="month" class="text-sm font-semibold">Month</label>
            <input id="month" name="month" type="number" min="1" max="12" value="{{ $month }}" class="hss-input mt-1" required>
        </div>
        <div>
            <label for="year" class="text-sm font-semibold">Year</label>
            <input id="year" name="year" type="number" min="2023" max="2100" value="{{ $year }}" class="hss-input mt-1" required>
        </div>
        <div class="flex items-end">
            <button class="hss-btn" type="submit" @disabled($departmentId === 0)>Generate preview</button>
        </div>
    </form>

    @if ($departments->isNotEmpty())
        <p class="mb-4 text-sm"><a class="underline" href="{{ route('rules.show', $departmentId) }}">Configure rules for this department</a></p>
    @endif

    @if ($run)
        <div class="mb-4 grid gap-4 lg:grid-cols-3">
            <section class="hss-card lg:col-span-2">
                <h2 class="font-semibold text-[#012970]">Fairness report · {{ $run->status->value }}</h2>
                <p class="text-sm text-slate-500">Spread between the busiest and quietest doctor: {{ $run->fairness['spread'] ?? 0 }} duties.</p>
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach ($run->fairness['doctors'] ?? [] as $doctor)
                        <li>Dr {{ $doctor['name'] }} · {{ $doctor['duties'] }}
                            @if (($doctor['holiday_duties'] ?? 0) > 0) · {{ $doctor['holiday_duties'] }} on a holiday @endif
                        </li>
                    @endforeach
                </ul>
                @if (! empty($run->fairness['coverage_gaps']))
                    <p class="mt-3 text-sm text-[#d65302]">{{ count($run->fairness['coverage_gaps']) }} coverage gap(s). The preview did not invent doctors to fill them.</p>
                @endif
                @foreach ($run->fairness['notes'] ?? [] as $note)
                    <p class="mt-2 text-sm text-slate-600">{{ $note }}</p>
                @endforeach
            </section>
            <section class="hss-card">
                @if ($run->status->value === 'preview')
                    <form method="POST" action="{{ route('runs.publish', $run) }}" data-confirm="Publish this roster for the department?">
                        @csrf
                        <button class="hss-btn hss-btn-teal w-full" type="submit">Publish</button>
                    </form>
                    <p class="mt-2 text-sm text-slate-500">Publishing replaces this department's {{ $type->label() }} duties for {{ $month }}/{{ $year }} only.</p>
                @else
                    <p class="font-semibold text-emerald-800">Published {{ $run->published_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                @endif
            </section>
        </div>
        @include('partials.legend')
        <div id="hss-calendar" class="hss-card mt-4" data-editable="1"
            data-events-url="{{ route('calendar.events', ['type' => $type->value, 'department_id' => $departmentId, 'run' => $run->id]) }}"
            data-resources-url="{{ route('calendar.resources', ['department_id' => $departmentId]) }}"></div>
    @else
        <p class="hss-card">No preview for this month yet.</p>
    @endif
@endsection
