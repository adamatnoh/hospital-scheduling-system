@extends('layouts.hss')
@section('heading', 'Subscribe to calendar')
@section('content')
    <x-ui.page-header title="Subscribe to calendar" icon="bi-calendar2-plus"
        subtitle="Keep your published duties in Google Calendar, Apple Calendar or Outlook." />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <x-ui.card title="Your private calendar link" icon="bi-link-45deg"
            subtitle="Anyone with this link can see your duties, so keep it to yourself.">
            <div x-data="{ copied: false, copy() { navigator.clipboard.writeText(this.$refs.url.value).then(() => { this.copied = true; setTimeout(() => this.copied = false, 2000) }) } }">
                <label for="feed-url" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted">Feed URL</label>
                <div class="flex flex-col gap-2 sm:flex-row">
                    <input id="feed-url" x-ref="url" type="text" readonly value="{{ $feedUrl }}" @focus="$el.select()"
                           class="hss-input min-w-0 flex-1 font-mono text-xs" data-feed-url>
                    <x-ui.button type="button" variant="outline" icon="bi-clipboard" x-on:click="copy()" x-bind:aria-label="copied ? 'Copied' : 'Copy link'">
                        <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                    </x-ui.button>
                </div>
                <p class="sr-only" role="status" aria-live="polite" x-text="copied ? 'Link copied' : ''"></p>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <a href="{{ $googleUrl }}" target="_blank" rel="noopener"
                   class="flex items-center gap-3 rounded-xl border border-line px-4 py-3 transition-colors hover:bg-subtle">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-subtle text-brand-600 ring-1 ring-line dark:text-brand-300" aria-hidden="true"><i class="bi bi-google"></i></span>
                    <span>
                        <span class="block text-sm font-semibold text-ink">Add to Google Calendar</span>
                        <span class="block text-xs text-muted">Opens Google Calendar in a new tab</span>
                    </span>
                </a>
                <a href="{{ $webcalUrl }}"
                   class="flex items-center gap-3 rounded-xl border border-line px-4 py-3 transition-colors hover:bg-subtle">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-subtle text-brand-600 ring-1 ring-line dark:text-brand-300" aria-hidden="true"><i class="bi bi-apple"></i></span>
                    <span>
                        <span class="block text-sm font-semibold text-ink">Add to Apple Calendar / Outlook</span>
                        <span class="block text-xs text-muted">Opens your calendar app</span>
                    </span>
                </a>
            </div>

            <p class="mt-5 flex items-start gap-2 rounded-xl bg-brand-50 px-4 py-3 text-sm text-brand-800 dark:bg-brand-900/30 dark:text-brand-200">
                <i class="bi bi-arrow-repeat mt-0.5" aria-hidden="true"></i>
                <span>Subscribed calendars update on their own. HSS asks apps to check every hour; Google Calendar can take up to a day to show changes.</span>
            </p>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card title="Reset link" icon="bi-shield-lock">
                <p class="text-sm text-muted">Shared the link by mistake? Make a new one. The old link stops working at once, so you will need to subscribe again.</p>
                <form method="POST" action="{{ route('calendar.subscribe.reset') }}" class="mt-4"
                      onsubmit="return confirm('Make a new calendar link? Calendars using the old link will stop updating.')">
                    @csrf
                    <x-ui.button variant="danger" size="sm" icon="bi-arrow-clockwise">Reset link</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="One-off file" icon="bi-download">
                <p class="text-sm text-muted">For apps that cannot subscribe. A downloaded file does not update.</p>
                <a href="{{ route('ical.download') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300">
                    <i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i>Download .ics
                </a>
            </x-ui.card>
        </div>
    </div>
@endsection
