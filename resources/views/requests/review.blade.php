@extends('layouts.hss')
@section('heading', 'Review Changes Requested')
@section('content')
    <div class="space-y-3">
        @forelse ($applications as $application)
            <article class="hss-card">
                <p class="font-semibold text-[#012970]">Dr {{ $application->user->name }} · {{ $application->type->label() }}</p>
                <p class="text-sm">{{ $application->start_date->format('d M Y') }} – {{ $application->end_date->format('d M Y') }}</p>
                <p class="mt-1 text-sm">{{ $application->reason }}</p>
                @if ($application->counterpart)
                    <p class="text-sm">Swap with Dr {{ $application->counterpart->name }}</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('review.decide', $application) }}">
                        @csrf
                        <input type="hidden" name="decision" value="approved">
                        <button class="hss-btn hss-btn-teal" type="submit">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('review.decide', $application) }}" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <input type="hidden" name="decision" value="rejected">
                        <div>
                            <label for="reason-{{ $application->id }}" class="text-sm font-semibold">Rejection reason</label>
                            <input id="reason-{{ $application->id }}" name="rejection_reason" required class="hss-input mt-1" aria-invalid="{{ $errors->has('rejection_reason') ? 'true' : 'false' }}">
                        </div>
                        <button class="rounded bg-[#d65302] px-3 py-2 font-semibold text-white" type="submit">Reject</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="hss-card">Nothing is waiting for review.</p>
        @endforelse
    </div>
@endsection
