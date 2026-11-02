<?php

use App\Models\Department;
use App\Services\AuditRecorder;
use Livewire\Volt\Component;

new class extends Component
{
    public int $departmentId;

    public int $doctors_per_on_call_day;

    public int $monthly_on_call_cap;

    public bool $rest_after_night;

    public bool $include_weekends;

    public int $ward_block_days;

    public string $holiday_state;

    public function mount(Department $department): void
    {
        abort_unless(auth()->user()?->can('updateRules', $department), 403);

        $this->departmentId = $department->id;
        $this->doctors_per_on_call_day = $department->doctors_per_on_call_day;
        $this->monthly_on_call_cap = $department->monthly_on_call_cap;
        $this->rest_after_night = $department->rest_after_night;
        $this->include_weekends = $department->include_weekends;
        $this->ward_block_days = $department->ward_block_days;
        $this->holiday_state = $department->holiday_state;
    }

    public function save(AuditRecorder $audit): void
    {
        $department = Department::query()->findOrFail($this->departmentId);
        abort_unless(auth()->user()?->can('updateRules', $department), 403);

        $validated = $this->validate([
            'doctors_per_on_call_day' => ['required', 'integer', 'min:1', 'max:6'],
            'monthly_on_call_cap' => ['required', 'integer', 'min:1', 'max:31'],
            'rest_after_night' => ['boolean'],
            'include_weekends' => ['boolean'],
            'ward_block_days' => ['required', 'integer', 'min:1', 'max:28'],
            'holiday_state' => ['required', 'string', 'max:50'],
        ]);

        $department->update($validated);
        $audit->record('rules.updated', $department, $validated, $department->id);
        session()->flash('status', 'Rules saved for '.$department->name.'.');
        $this->js('window.hssToast?.('.json_encode('Rules saved for '.$department->name.'.').')');
    }
}; ?>

<form wire:submit="save" class="space-y-8">
    <fieldset>
        <legend class="text-sm font-semibold text-ink">Staffing limits</legend>
        <p class="text-xs text-muted">How many doctors the generator puts on each duty.</p>
        <div class="mt-4 grid gap-5 sm:grid-cols-3">
            <x-ui.field label="Doctors on call each day" for="doctors_per_on_call_day">
                <input id="doctors_per_on_call_day" type="number" min="1" max="6" wire:model="doctors_per_on_call_day" class="hss-input" />
            </x-ui.field>
            <x-ui.field label="Monthly on-call cap" for="monthly_on_call_cap" hint="Per doctor, per month.">
                <input id="monthly_on_call_cap" type="number" min="1" max="31" wire:model="monthly_on_call_cap" class="hss-input" />
            </x-ui.field>
            <x-ui.field label="Ward block length (days)" for="ward_block_days">
                <input id="ward_block_days" type="number" min="1" max="28" wire:model="ward_block_days" class="hss-input" />
            </x-ui.field>
        </div>
    </fieldset>

    <fieldset class="border-t border-line pt-6">
        <legend class="sr-only">Calendar</legend>
        <div class="grid gap-5 sm:grid-cols-3">
            <x-ui.field label="Holiday state" for="holiday_state" hint="Which state's public holidays apply.">
                <select id="holiday_state" wire:model="holiday_state" class="hss-input">
                    @foreach (config('hss.states') as $state)
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <div class="space-y-3 sm:col-span-2 sm:pt-6">
                <label class="flex items-start gap-3 rounded-xl border border-line p-3 hover:bg-subtle">
                    <input type="checkbox" wire:model="include_weekends" class="hss-check mt-0.5" />
                    <span><span class="block text-sm font-medium text-ink">Weekend shifts</span><span class="block text-xs text-muted">Schedule Morning, Evening and Night on weekends.</span></span>
                </label>
                <label class="flex items-start gap-3 rounded-xl border border-line p-3 hover:bg-subtle">
                    <input type="checkbox" wire:model="rest_after_night" class="hss-check mt-0.5" />
                    <span><span class="block text-sm font-medium text-ink">Rest after nights</span><span class="block text-xs text-muted">Keep the day after a night shift free.</span></span>
                </label>
            </div>
        </div>
    </fieldset>

    <div class="flex items-center gap-3 border-t border-line pt-6">
        <x-ui.button icon="bi-check-lg" wire:loading.attr="disabled">Save rules</x-ui.button>
        <span wire:loading wire:target="save" class="text-sm text-muted"><i class="bi bi-arrow-repeat animate-spin" aria-hidden="true"></i> Saving…</span>
    </div>
</form>
