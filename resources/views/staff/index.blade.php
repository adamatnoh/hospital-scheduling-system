@extends('layouts.hss')
@section('heading', 'Staff')
@section('content')
    <form method="POST" action="{{ route('staff.store') }}" class="hss-card mb-4 grid gap-3 md:grid-cols-3">
        @csrf
        <div>
            <label for="name" class="text-sm font-semibold">Name</label>
            <input id="name" name="name" required class="hss-input mt-1" value="{{ old('name') }}">
        </div>
        <div>
            <label for="email" class="text-sm font-semibold">Email</label>
            <input id="email" name="email" type="email" required class="hss-input mt-1" value="{{ old('email') }}">
        </div>
        <div>
            <label for="password" class="text-sm font-semibold">Password</label>
            <input id="password" name="password" type="password" required minlength="8" class="hss-input mt-1">
        </div>
        <div>
            <label for="role" class="text-sm font-semibold">Role</label>
            <select id="role" name="role" class="hss-input mt-1">
                <option value="regular">Doctor</option>
                <option value="scheduler">Scheduler</option>
            </select>
        </div>
        <div>
            <label for="department_id" class="text-sm font-semibold">Department</label>
            <select id="department_id" name="department_id" class="hss-input mt-1" required>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><button class="hss-btn" type="submit">Add staff</button></div>
    </form>
    <div class="hss-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b text-[#012970]"><th class="py-2">Name</th><th>Email</th><th>Role</th><th>Department</th><th></th></tr></thead>
            <tbody>
                @foreach ($staff as $person)
                    <tr class="border-b">
                        <td class="py-2">{{ $person->displayName() }}</td>
                        <td>{{ $person->email }}</td>
                        <td>{{ $person->role?->label() }}</td>
                        <td>{{ $person->department?->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('staff.destroy', $person) }}" data-confirm="Remove this staff account?">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-[#d65302]" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
