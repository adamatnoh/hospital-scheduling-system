@extends('layouts.hss')
@section('heading', 'Review Changes Requested')
@section('content')
    <x-ui.page-header title="Review requests" icon="bi-inbox" :subtitle="$applications->count().' waiting for a decision'">
        <x-slot:actions><x-ui.button variant="outline" icon="bi-journal-check" :href="route('reviewHistory')">Decision history</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    @if ($applications->isEmpty())
        <x-ui.card><x-ui.empty icon="bi-check2-all" title="All caught up">Nothing is waiting for review.</x-ui.empty></x-ui.card>
    @else
        <div class="grid gap-4 xl:grid-cols-2">
            @foreach ($applications as $application)
                @php($typeIcon = match ($application->type->value) { 'leave' => 'bi-airplane', 'swap' => 'bi-arrow-left-right', default => 'bi-moon-stars' })
                <article id="request-{{ $application->id }}" class="hss-card scroll-mt-24 target:ring-2 target:ring-brand-400">
                    <div class="flex items-start gap-4">
                        <x-ui.avatar :name="$application->user->name" size="lg" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-ink">Dr {{ $application->user->name }}</h2>
                                <x-ui.badge tone="navy" :icon="$typeIcon">{{ $application->type->label() }}</x-ui.badge>
                                <x-ui.request-status :status="$application->status" />
                            </div>
                            <p class="mt-1 flex items-center gap-1.5 text-sm text-muted"><i class="bi bi-calendar-range" aria-hidden="true"></i>{{ $application->start_date->format('D, d M Y') }} – {{ $application->end_date->format('D, d M Y') }}
                                <span class="text-xs">({{ $application->start_date->diffInDays($application->end_date) + 1 }} day{{ $application->start_date->diffInDays($application->end_date) ? 's' : '' }})</span></p>
                            @if ($application->counterpart)
                                <p class="mt-1 flex items-center gap-1.5 text-sm text-muted"><i class="bi bi-arrow-left-right" aria-hidden="true"></i>Swap with Dr {{ $application->counterpart->name }}</p>
                            @endif
                            <blockquote class="mt-3 rounded-xl bg-subtle p-3 text-sm text-ink ring-1 ring-line">{{ $application->reason }}</blockquote>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-line pt-4">
                        <x-ui.button variant="outline" type="button" icon="bi-x-lg" class="!text-rose-700 dark:!text-rose-300" x-data x-on:click="$dispatch('open-modal', 'reject-{{ $application->id }}')">Reject…</x-ui.button>
                        <form method="POST" action="{{ route('review.decide', $application) }}">
                            @csrf
                            <input type="hidden" name="decision" value="approved">
                            <x-ui.button icon="bi-check-lg">Approve</x-ui.button>
                        </form>
                    </div>
                </article>

                <x-ui.modal :name="'reject-'.$application->id" :title="'Reject Dr '.$application->user->name.'\'s request'">
                    <form method="POST" action="{{ route('review.decide', $application) }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <x-ui.field label="Rejection reason" for="reason-{{ $application->id }}" name="rejection_reason" required hint="The doctor sees this note in their notification.">
                            <textarea id="reason-{{ $application->id }}" name="rejection_reason" rows="3" required class="hss-input" aria-invalid="{{ $errors->has('rejection_reason') ? 'true' : 'false' }}"></textarea>
                        </x-ui.field>
                        <div class="flex justify-end gap-2">
                            <x-ui.button variant="ghost" type="button" x-on:click="$dispatch('close-modal', 'reject-{{ $application->id }}')">Cancel</x-ui.button>
                            <x-ui.button variant="danger" icon="bi-x-lg">Reject request</x-ui.button>
                        </div>
                    </form>
                </x-ui.modal>
            @endforeach
        </div>
    @endif
@endsection
