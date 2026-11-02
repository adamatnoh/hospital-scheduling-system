@extends('layouts.hss')
@section('heading', ($department->name ?? 'Department').' '.$type->label())
@section('content')
    <x-ui.page-header :title="$type->label().' schedule'" :subtitle="($department->name ?? 'No department').' · published roster'"
        :icon="match ($type->value) { 'on_call' => 'bi-moon-stars', 'ward' => 'bi-hospital', default => 'bi-clock' }">
        <x-slot:actions>
            @if (auth()->user()->isAdmin() && $departments->count() > 1)
                <form method="GET" class="flex items-center gap-2">
                    <label for="department_id" class="sr-only">Department</label>
                    <select id="department_id" name="department_id" class="hss-input !w-auto !py-2" onchange="this.form.submit()">
                        @foreach ($departments as $option)
                            <option value="{{ $option->id }}" @selected($option->id === $department?->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    <noscript><x-ui.button size="sm">Go</x-ui.button></noscript>
                </form>
            @endif
            @if ($department)
                <x-ui.button variant="outline" icon="bi-filetype-pdf" :href="route('schedules.export', ['type' => $type->value, 'department_id' => $department->id, 'format' => 'pdf'])">PDF</x-ui.button>
                <x-ui.button variant="outline" icon="bi-file-earmark-excel" :href="route('schedules.export', ['type' => $type->value, 'department_id' => $department->id, 'format' => 'xlsx'])">Excel</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    @if ($department)
        <x-ui.card>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                @include('partials.legend')
                <p class="flex items-center gap-1.5 text-xs text-muted"><i class="bi bi-info-circle" aria-hidden="true"></i>Schedulers can drag a duty onto another day. A move that double-books a doctor is refused.</p>
            </div>
            <div id="hss-calendar"
                data-editable="{{ auth()->user()->can('generate', [\App\Models\ScheduleRun::class, $department]) ? '1' : '0' }}"
                data-events-url="{{ route('calendar.events', ['type' => $type->value, 'department_id' => $department->id]) }}"
                data-resources-url="{{ route('calendar.resources', ['department_id' => $department->id]) }}"></div>
        </x-ui.card>
    @else
        <x-ui.card><x-ui.empty icon="bi-building" title="No department yet">An administrator needs to set up a department first.</x-ui.empty></x-ui.card>
    @endif
@endsection
