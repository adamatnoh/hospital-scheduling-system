@extends('layouts.hss')
@section('heading', 'Public Holidays')
@section('content')
    <form method="POST" action="{{ route('holidays.store') }}" class="hss-card mb-4 grid gap-3 md:grid-cols-4">
        @csrf
        <div>
            <label for="date" class="text-sm font-semibold">Date</label>
            <input id="date" name="date" type="date" required class="hss-input mt-1">
        </div>
        <div>
            <label for="name" class="text-sm font-semibold">Name</label>
            <input id="name" name="name" required class="hss-input mt-1">
        </div>
        <div>
            <label for="state" class="text-sm font-semibold">State</label>
            <select id="state" name="state" class="hss-input mt-1">
                @foreach ($states as $state)
                    <option value="{{ $state }}">{{ $state }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end"><button class="hss-btn" type="submit">Save holiday</button></div>
    </form>
    <div class="hss-card overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b text-[#012970]"><th class="py-2">Date</th><th>Name</th><th>State</th><th></th></tr></thead>
            <tbody>
                @foreach ($holidays as $holiday)
                    <tr class="border-b">
                        <td class="py-2">{{ $holiday->date->format('d M Y') }}</td>
                        <td>{{ $holiday->name }}</td>
                        <td>{{ $holiday->state }}</td>
                        <td>
                            <form method="POST" action="{{ route('holidays.destroy', $holiday) }}">
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
