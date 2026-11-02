@extends('layouts.hss')
@section('heading', 'Dashboard')
@section('content')
    <div class="grid gap-4 md:grid-cols-3">
        <article class="hss-card">
            <p class="text-sm text-slate-500">Pending requests</p>
            <p class="text-3xl font-semibold text-[#012970]">{{ $stats['pending'] }}</p>
        </article>
        <article class="hss-card">
            <p class="text-sm text-slate-500">On-call coverage gaps this month</p>
            <p class="text-3xl font-semibold text-[#d65302]">{{ $stats['gap_count'] }}</p>
        </article>
        <article class="hss-card">
            <p class="text-sm text-slate-500">Your next duty</p>
            <p class="text-lg font-semibold">
                @if ($stats['upcoming']->isEmpty())
                    Nothing published yet
                @else
                    {{ $stats['upcoming']->first()->date->format('d M') }} · {{ $stats['upcoming']->first()->type->label() }}
                @endif
            </p>
        </article>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="hss-card">
            <h2 class="mb-3 font-semibold text-[#012970]">Workload this month</h2>
            @php($max = max(1, (int) collect($stats['workload'])->max('duties')))
            <ul class="space-y-2">
                @forelse ($stats['workload'] as $row)
                    <li>
                        <div class="flex justify-between text-sm"><span>{{ $row['name'] }}</span><span>{{ $row['duties'] }}</span></div>
                        <div class="h-2 rounded bg-slate-100"><div class="h-2 rounded bg-[#8da399]" style="width: {{ round($row['duties'] / $max * 100) }}%"></div></div>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Publish a roster to see the workload.</li>
                @endforelse
            </ul>
        </section>
        <section class="hss-card">
            <h2 class="mb-3 font-semibold text-[#012970]">Coverage gaps</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['gaps'] as $gap)
                    <li>{{ $gap['department'] }} · {{ \Illuminate\Support\Carbon::parse($gap['date'])->format('d M') }} has {{ $gap['assigned'] }} of {{ $gap['needed'] }} on call.</li>
                @empty
                    <li class="text-slate-500">No on-call gaps in the published month, or no roster yet.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
