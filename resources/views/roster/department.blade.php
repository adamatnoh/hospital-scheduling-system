@extends('layouts.hss')
@section('heading', ($department->name ?? 'Department').' '.$type->label())
@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        @include('partials.legend')
        <div class="flex flex-wrap gap-2">
            @if ($department)
                <a class="hss-btn" href="{{ route('schedules.export', ['type' => $type->value, 'department_id' => $department->id, 'format' => 'pdf']) }}">PDF</a>
                <a class="hss-btn hss-btn-teal" href="{{ route('schedules.export', ['type' => $type->value, 'department_id' => $department->id, 'format' => 'xlsx']) }}">Excel</a>
            @endif
        </div>
    </div>
    @if (auth()->user()->isAdmin() && $departments->count() > 1)
        <form method="GET" class="mb-4">
            <label for="department_id" class="text-sm font-semibold">Department</label>
            <select id="department_id" name="department_id" class="hss-input mt-1 max-w-xs" onchange="this.form.submit()">
                @foreach ($departments as $option)
                    <option value="{{ $option->id }}" @selected($option->id === $department?->id)>{{ $option->name }}</option>
                @endforeach
            </select>
        </form>
    @endif
    @if ($department)
        <div id="hss-calendar" class="hss-card"
            data-editable="{{ auth()->user()->can('generate', [\App\Models\ScheduleRun::class, $department]) ? '1' : '0' }}"
            data-events-url="{{ route('calendar.events', ['type' => $type->value, 'department_id' => $department->id]) }}"
            data-resources-url="{{ route('calendar.resources', ['department_id' => $department->id]) }}"></div>
        <p class="mt-2 text-sm text-slate-500">Schedulers can drag a duty onto another day. The roster refuses a move that double-books that doctor.</p>
    @else
        <p class="hss-card">No department has been set up yet.</p>
    @endif
@endsection
