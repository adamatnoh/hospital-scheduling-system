@extends('layouts.hss')
@section('heading', 'Staff')
@section('content')
    <x-ui.page-header title="Staff directory" icon="bi-people" :subtitle="$staff->count().' doctors and schedulers'.($search !== '' ? ' matching “'.$search.'”' : '')">
        <x-slot:actions>
            <form method="GET" class="relative" role="search">
                <label for="staff-q" class="sr-only">Search staff</label>
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted" aria-hidden="true"></i>
                <input id="staff-q" name="q" value="{{ $search }}" placeholder="Search name or email" class="hss-input !w-64 !py-2 pl-9">
            </form>
            <x-ui.button icon="bi-person-plus" type="button" x-data x-on:click="$dispatch('open-modal', 'staff-create')">Add staff</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card flush>
        @if ($staff->isEmpty())
            <x-ui.empty icon="bi-people" title="{{ $search !== '' ? 'No one matches that search' : 'No staff yet' }}">
                @if ($search !== '')<a class="font-semibold text-brand-700 hover:underline" href="{{ route('manageStaff') }}">Clear the search</a>@else Add a doctor or scheduler to get started.@endif
            </x-ui.empty>
        @else
            <x-ui.table caption="Staff accounts">
                <x-slot:head><th>Name</th><th>Role</th><th>Department</th><th class="text-right">Actions</th></x-slot:head>
                @foreach ($staff as $person)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :name="$person->name" />
                                <div class="min-w-0">
                                    <p class="font-medium text-ink">{{ $person->displayName() }}</p>
                                    <p class="truncate text-xs text-muted">{{ $person->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td><x-ui.badge :tone="$person->role?->value === 'scheduler' ? 'brand' : 'navy'" :icon="$person->role?->value === 'scheduler' ? 'bi-calendar2-week' : 'bi-heart-pulse'">{{ $person->role?->label() }}</x-ui.badge></td>
                        <td>{{ $person->department?->name ?? '—' }}</td>
                        <td>
                            <div class="flex justify-end gap-1">
                                <x-ui.button variant="ghost" size="sm" type="button" icon="bi-pencil" x-data x-on:click="$dispatch('open-modal', 'staff-edit-{{ $person->id }}')">Edit</x-ui.button>
                                <form method="POST" action="{{ route('staff.destroy', $person) }}" data-confirm="Remove {{ $person->displayName() }}'s account?" data-confirm-text="Remove" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button variant="ghost" size="sm" icon="bi-trash3" class="!text-rose-700 dark:!text-rose-300">Remove</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        @endif
    </x-ui.card>

    <x-ui.modal name="staff-create" title="Add a staff account">
        <form method="POST" action="{{ route('staff.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <x-ui.field label="Name" for="name" required class="sm:col-span-2"><input id="name" name="name" required class="hss-input" value="{{ old('name') }}"></x-ui.field>
            <x-ui.field label="Email" for="email" required><input id="email" name="email" type="email" required class="hss-input" value="{{ old('email') }}"></x-ui.field>
            <x-ui.field label="Password" for="password" required hint="At least 8 characters."><input id="password" name="password" type="password" required minlength="8" class="hss-input"></x-ui.field>
            <x-ui.field label="Role" for="role">
                <select id="role" name="role" class="hss-input">
                    <option value="regular" @selected(old('role') === 'regular')>Doctor</option>
                    <option value="scheduler" @selected(old('role') === 'scheduler')>Scheduler</option>
                </select>
            </x-ui.field>
            <x-ui.field label="Department" for="department_id" required>
                <select id="department_id" name="department_id" class="hss-input" required>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <div class="flex justify-end gap-2 sm:col-span-2">
                <x-ui.button variant="ghost" type="button" x-on:click="$dispatch('close-modal', 'staff-create')">Cancel</x-ui.button>
                <x-ui.button icon="bi-person-plus">Add staff</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    @foreach ($staff as $person)
        <x-ui.modal :name="'staff-edit-'.$person->id" :title="'Edit '.$person->displayName()">
            <form method="POST" action="{{ route('staff.update', $person) }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <x-ui.field label="Name" for="name-{{ $person->id }}" name="name" required class="sm:col-span-2"><input id="name-{{ $person->id }}" name="name" required class="hss-input" value="{{ $person->name }}"></x-ui.field>
                <x-ui.field label="Email" for="email-{{ $person->id }}" name="email" required class="sm:col-span-2"><input id="email-{{ $person->id }}" name="email" type="email" required class="hss-input" value="{{ $person->email }}"></x-ui.field>
                <x-ui.field label="Role" for="role-{{ $person->id }}" name="role">
                    <select id="role-{{ $person->id }}" name="role" class="hss-input">
                        <option value="regular" @selected($person->role?->value === 'regular')>Doctor</option>
                        <option value="scheduler" @selected($person->role?->value === 'scheduler')>Scheduler</option>
                    </select>
                </x-ui.field>
                <x-ui.field label="Department" for="department-{{ $person->id }}" name="department_id">
                    <select id="department-{{ $person->id }}" name="department_id" class="hss-input" required>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($person->department_id === $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <div class="flex justify-end gap-2 sm:col-span-2">
                    <x-ui.button variant="ghost" type="button" x-on:click="$dispatch('close-modal', 'staff-edit-{{ $person->id }}')">Cancel</x-ui.button>
                    <x-ui.button icon="bi-check-lg">Save changes</x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    @endforeach

    @if ($errors->any() && old('_method') !== 'PUT')
        <script>document.addEventListener('alpine:initialized', () => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'staff-create' })));</script>
    @endif
@endsection
