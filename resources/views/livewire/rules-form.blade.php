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
    }
}; ?>

<form wire:submit="save" class="grid gap-4 max-w-xl">
    <div>
        <label for="doctors_per_on_call_day" class="block text-sm font-semibold">Doctors on call each day</label>
        <input id="doctors_per_on_call_day" type="number" min="1" max="6" wire:model="doctors_per_on_call_day" class="mt-1 w-full rounded border-gray-300" />
        @error('doctors_per_on_call_day') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="monthly_on_call_cap" class="block text-sm font-semibold">Monthly on-call cap</label>
        <input id="monthly_on_call_cap" type="number" min="1" max="31" wire:model="monthly_on_call_cap" class="mt-1 w-full rounded border-gray-300" />
        @error('monthly_on_call_cap') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="ward_block_days" class="block text-sm font-semibold">Ward block length (days)</label>
        <input id="ward_block_days" type="number" min="1" max="28" wire:model="ward_block_days" class="mt-1 w-full rounded border-gray-300" />
        @error('ward_block_days') <p class="text-sm text-red-700">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="holiday_state" class="block text-sm font-semibold">Holiday state</label>
        <select id="holiday_state" wire:model="holiday_state" class="mt-1 w-full rounded border-gray-300">
            @foreach (config('hss.states') as $state)
                <option value="{{ $state }}">{{ $state }}</option>
            @endforeach
        </select>
    </div>
    <label class="flex items-center gap-2">
        <input type="checkbox" wire:model="include_weekends" />
        <span>Schedule Morning, Evening and Night on weekends</span>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" wire:model="rest_after_night" />
        <span>Rest the day after a night shift</span>
    </label>
    <button type="submit" class="rounded bg-[#012970] px-4 py-2 text-white">Save rules</button>
</form>
