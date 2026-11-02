<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('hss.name') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; color: #012970; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background: #012970; color: white; }
    </style>
</head>
<body>
    <h1>{{ config('hss.hospital') }}</h1>
    <p>{{ $department->name }} {{ $type->label() }} roster, {{ $month->format('F Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Doctor</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($assignments as $assignment)
                <tr>
                    <td>{{ $assignment->date->format('d M Y') }}</td>
                    <td>Dr {{ $assignment->user->name }}</td>
                    <td>{{ $assignment->shift?->value ?? $assignment->ward?->name ?? $assignment->type->label() }}</td>
                </tr>
            @empty
                <tr><td colspan="3">No published duties for this month.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
