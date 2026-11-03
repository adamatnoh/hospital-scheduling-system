@extends('layouts.hss')
@section('heading', 'Dashboard')
@section('content')
    @php
        $user = auth()->user();
        $staff = $user->isAdmin() || $user->isScheduler();
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $trend = $stats['duties_previous'] > 0 ? (int) round(($stats['duties_month'] - $stats['duties_previous']) / $stats['duties_previous'] * 100) : null;
        $next = $stats['upcoming']->first();
        $typeTone = fn ($assignment) => match ($assignment->type->value) {
            'on_call' => 'night',
            'ward' => 'brand',
            default => match ($assignment->shift?->value) { 'Morning' => 'morning', 'Evening' => 'evening', default => 'night' },
        };
        $chart = [
            'labels' => collect($stats['workload'])->take(12)->pluck('name'),
            'datasets' => [
                ['label' => 'On call', 'data' => collect($stats['workload'])->take(12)->pluck('on_call'), 'backgroundColor' => '#311F62'],
                ['label' => 'Ward', 'data' => collect($stats['workload'])->take(12)->pluck('ward'), 'backgroundColor' => '#1d9a95'],
                ['label' => 'Shift', 'data' => collect($stats['workload'])->take(12)->pluck('shift'), 'backgroundColor' => '#8da399'],
            ],
        ];
    @endphp

    {{-- Welcome --}}
    <section class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-navy-900 via-navy-800 to-brand-700 p-6 text-white shadow-[var(--shadow-raised)] sm:p-7">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>
        <svg class="pointer-events-none absolute bottom-0 right-0 h-28 w-[28rem] opacity-20" viewBox="0 0 448 112" fill="none" aria-hidden="true">
            <path d="M0 70h150l14-30 22 60 20-80 18 50h224" stroke="#a9e8e2" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="relative flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-brand-200">{{ now()->format('l, j F Y') }}</p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{{ $greeting }}, {{ $user->displayName() }}</h2>
                <p class="mt-1 max-w-xl text-sm text-navy-100">
                    @if ($staff)
                        {{ $stats['pending'] }} {{ \Illuminate\Support\Str::plural('request', $stats['pending']) }} waiting for review and {{ $stats['gap_upcoming'] }} upcoming on-call {{ \Illuminate\Support\Str::plural('gap', $stats['gap_upcoming']) }} this month.
                    @elseif ($next)
                        Your next duty is {{ $next->type->label() }} on {{ $next->date->format('l, j M') }}.
                    @else
                        No published duties for you yet. Check back once your scheduler publishes the roster.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($staff)
                    <a href="{{ route('generate.show', 'on_call') }}" class="hss-btn bg-white !text-navy-900 hover:!bg-brand-50"><i class="bi bi-magic" aria-hidden="true"></i>Generate roster</a>
                    <a href="{{ route('review') }}" class="hss-btn border-white/20 bg-white/10 hover:bg-white/20"><i class="bi bi-inbox" aria-hidden="true"></i>Review requests</a>
                @else
                    <a href="{{ route('calendar.leave') }}" class="hss-btn bg-white !text-navy-900 hover:!bg-brand-50"><i class="bi bi-calendar-x" aria-hidden="true"></i>Apply leave</a>
                    <a href="{{ route('swaps.create') }}" class="hss-btn border-white/20 bg-white/10 hover:bg-white/20"><i class="bi bi-arrow-left-right" aria-hidden="true"></i>Request swap</a>
                @endif
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if ($staff)
            <x-ui.stat label="Pending requests" :value="$stats['pending']" icon="bi-inbox" tone="amber" :href="route('review')"
                       :hint="$stats['pending'] ? 'Awaiting your decision' : 'Inbox zero'" />
        @else
            <x-ui.stat label="Your next duty" :value="$next ? $next->date->format('d M') : '—'" icon="bi-calendar-check" tone="brand" :href="route('calendar.yourschedule')"
                       :hint="$next ? $next->type->label().($next->ward ? ' · '.$next->ward->name : '').($next->shift ? ' · '.$next->shift->value : '') : 'Nothing published yet'" />
        @endif
        <x-ui.stat label="Coverage gaps this month" :value="$stats['gap_count']" icon="bi-shield-exclamation" :tone="$stats['gap_critical'] ? 'rose' : 'evening'"
                   :hint="$stats['gap_critical'].' with nobody on call · '.$stats['gap_upcoming'].' upcoming'" :href="route('calendar.department', 'on_call')" />
        <x-ui.stat label="Duties published this month" :value="$stats['duties_month']" icon="bi-calendar3" tone="navy" :trend="$trend"
                   :hint="$trend === null ? 'No data for last month' : 'vs '.$stats['duties_previous'].' last month'" />
        <x-ui.stat label="On duty today" :value="$stats['on_duty_today']" icon="bi-heart-pulse" tone="night"
                   :hint="'of '.$stats['staff_count'].' '.\Illuminate\Support\Str::plural('doctor', $stats['staff_count']).' in '.($user->isAdmin() ? 'all departments' : ($user->department->name ?? 'your department'))" />
    </div>

    {{-- Announcements + timeline --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="min-w-0 xl:col-span-2">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="flex items-center gap-2 font-semibold text-ink"><i class="bi bi-megaphone text-brand-600 dark:text-brand-300" aria-hidden="true"></i>Announcements</h3>
                @if ($user->isAdmin())
                    <a href="{{ route('announcements.index') }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">Manage</a>
                @endif
            </div>
            <x-announcement-carousel :announcements="$announcements" />
        </div>

        <x-ui.card :title="$user->isAdmin() ? 'Upcoming on-call' : 'Your upcoming duties'" :subtitle="$user->isAdmin() ? 'Next published on-call doctors' : 'Published roster'" icon="bi-clock-history">
            <x-slot:actions>
                <a href="{{ route('calendar.yourschedule') }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">Calendar</a>
            </x-slot:actions>
            @if ($stats['timeline']->isEmpty())
                <x-ui.empty icon="bi-calendar2-week" title="No upcoming duties">Published duties will appear here as a timeline.</x-ui.empty>
            @else
                <ol class="relative ml-2 space-y-4 border-l-2 border-line pl-6">
                    @foreach ($stats['timeline'] as $duty)
                        <li class="relative">
                            <span class="absolute -left-[1.95rem] top-1 grid h-4 w-4 place-items-center rounded-full bg-surface ring-2 {{ $duty->date->isToday() ? 'ring-brand-500' : 'ring-line' }}" aria-hidden="true">
                                <span class="h-1.5 w-1.5 rounded-full {{ $duty->date->isToday() ? 'bg-brand-500' : 'bg-muted' }}"></span>
                            </span>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-ink">
                                    <time datetime="{{ $duty->date->toDateString() }}">{{ $duty->date->isToday() ? 'Today' : ($duty->date->isTomorrow() ? 'Tomorrow' : $duty->date->format('D, d M')) }}</time>
                                </p>
                                <x-ui.badge :tone="$typeTone($duty)">{{ $duty->type->value === 'shift' ? $duty->shift?->value : $duty->type->label() }}</x-ui.badge>
                            </div>
                            <p class="text-sm text-muted">
                                @if ($user->isAdmin()) Dr {{ $duty->user->name }} · @endif
                                {{ $duty->department->name ?? '' }}{{ $duty->ward ? ' · '.$duty->ward->name : '' }}
                            </p>
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-ui.card>
    </div>

    {{-- Workload + gaps --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2" title="Workload this month" :subtitle="now()->format('F Y').' · published duties per doctor'" icon="bi-bar-chart-line">
            @if (collect($stats['workload'])->isEmpty())
                <x-ui.empty icon="bi-bar-chart" title="No published roster yet">Generate and publish a roster to see how duties are shared.</x-ui.empty>
            @else
                <div class="relative" style="height: {{ max(220, min(12, count($stats['workload'])) * 34 + 70) }}px">
                    <canvas data-chart='@json($chart)' role="img" aria-label="Stacked bar chart of on call, ward and shift duties per doctor this month"></canvas>
                </div>
                <details class="mt-3 text-sm">
                    <summary class="cursor-pointer text-muted">Show as table</summary>
                    <x-ui.table class="mt-2" caption="Workload this month">
                        <x-slot:head><th>Doctor</th><th>On call</th><th>Ward</th><th>Shift</th><th>Total</th></x-slot:head>
                        @foreach ($stats['workload'] as $row)
                            <tr><td>{{ $row['name'] }}</td><td>{{ $row['on_call'] }}</td><td>{{ $row['ward'] }}</td><td>{{ $row['shift'] }}</td><td class="font-semibold">{{ $row['duties'] }}</td></tr>
                        @endforeach
                    </x-ui.table>
                </details>
            @endif
        </x-ui.card>

        <x-ui.card title="Coverage gaps" subtitle="On-call days below the required doctors" icon="bi-shield-exclamation" flush>
            <x-slot:actions>
                @if ($stats['gap_count'])<x-ui.badge :tone="$stats['gap_critical'] ? 'rose' : 'amber'">{{ $stats['gap_count'] }} total</x-ui.badge>@endif
            </x-slot:actions>
            @if (empty($stats['gaps']))
                <x-ui.empty icon="bi-shield-check" title="Fully covered">Every on-call day this month has enough doctors.</x-ui.empty>
            @else
                <ul class="divide-y divide-line">
                    @foreach ($stats['gaps'] as $gap)
                        @php($date = \Illuminate\Support\Carbon::parse($gap['date']))
                        <li class="flex items-center gap-3 px-5 py-3 {{ $gap['upcoming'] ? '' : 'opacity-60' }}">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl {{ $gap['severity'] === 'critical' ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : 'bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' }}">
                                <span class="text-center leading-none"><span class="block text-[0.6rem] font-semibold uppercase">{{ $date->format('M') }}</span><span class="block text-base font-bold">{{ $date->format('d') }}</span></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink">{{ $gap['department'] }}</p>
                                <p class="text-xs text-muted">{{ $date->format('l') }} · {{ $gap['assigned'] }} of {{ $gap['needed'] }} on call{{ $gap['upcoming'] ? '' : ' · past' }}</p>
                            </div>
                            <x-ui.badge :tone="$gap['severity'] === 'critical' ? 'rose' : 'amber'" dot>{{ $gap['severity'] === 'critical' ? 'Critical' : 'Short' }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($stats['gap_count'] > count($stats['gaps']))
                <x-slot:footer>
                    <a href="{{ route('calendar.department', 'on_call') }}" class="text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">View all {{ $stats['gap_count'] }} in the on-call calendar →</a>
                </x-slot:footer>
            @endif
        </x-ui.card>
    </div>

    {{-- Requests --}}
    <div class="mt-6">
        @if ($staff)
            <x-ui.card title="Pending requests" subtitle="Oldest first · approve here or open the review queue" icon="bi-inbox" flush>
                <x-slot:actions>
                    <x-ui.button variant="outline" size="sm" :href="route('review')" icon="bi-box-arrow-up-right">Open review queue</x-ui.button>
                </x-slot:actions>
                @if ($stats['pending_list']->isEmpty())
                    <x-ui.empty icon="bi-check2-all" title="Nothing waiting for review">New leave, on-call and swap requests will land here.</x-ui.empty>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($stats['pending_list'] as $application)
                            <li class="flex flex-wrap items-center gap-4 px-5 py-3.5">
                                <x-ui.avatar :name="$application->user->name" />
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-ink">Dr {{ $application->user->name }}
                                        <x-ui.badge :tone="match ($application->type->value) { 'leave' => 'brand', 'swap' => 'violet', default => 'night' }" class="ml-1">{{ $application->type->label() }}</x-ui.badge>
                                    </p>
                                    <p class="truncate text-xs text-muted">
                                        {{ $application->start_date->format('d M') }}{{ $application->end_date && ! $application->end_date->eq($application->start_date) ? ' – '.$application->end_date->format('d M Y') : ' '.$application->start_date->format('Y') }}
                                        @if ($application->counterpart) · swap with Dr {{ $application->counterpart->name }} @endif
                                        @if ($application->reason) · “{{ \Illuminate\Support\Str::limit($application->reason, 60) }}” @endif
                                        · {{ $application->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('review.decide', $application) }}" data-confirm="Approve this {{ strtolower($application->type->label()) }} request?" data-confirm-text="Dr {{ $application->user->name }} will be notified.">
                                        @csrf
                                        <input type="hidden" name="decision" value="approved">
                                        <x-ui.button size="sm" icon="bi-check-lg">Approve</x-ui.button>
                                    </form>
                                    <x-ui.button variant="outline" size="sm" :href="route('review').'#request-'.$application->id">Review</x-ui.button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        @else
            <x-ui.card title="Your recent requests" subtitle="Leave, on-call and swap applications" icon="bi-send" flush>
                <x-slot:actions>
                    <x-ui.button variant="outline" size="sm" :href="route('calendar.history')">Full history</x-ui.button>
                </x-slot:actions>
                @if ($stats['mine']->isEmpty())
                    <x-ui.empty icon="bi-send" title="No requests yet">Apply for leave, an on-call day or a swap from the sidebar.</x-ui.empty>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($stats['mine'] as $application)
                            <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-ink">{{ $application->type->label() }}</p>
                                    <p class="text-xs text-muted">{{ $application->start_date->format('d M Y') }}{{ $application->end_date && ! $application->end_date->eq($application->start_date) ? ' – '.$application->end_date->format('d M Y') : '' }}</p>
                                </div>
                                <x-ui.request-status :status="$application->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        @endif
    </div>
@endsection
