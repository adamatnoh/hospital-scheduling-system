@extends('layouts.hss')
@section('heading', 'Announcements')
@section('content')
    <x-ui.page-header title="Announcements" icon="bi-megaphone"
        :subtitle="$live.' live on the dashboard and landing page · '.$announcements->count().' total'">
        <x-slot:actions>
            <x-ui.button :href="route('announcements.create')" icon="bi-plus-lg">New announcement</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_26rem]">
        <x-ui.card flush>
            @if ($announcements->isEmpty())
                <x-ui.empty icon="bi-megaphone" title="No announcements yet">
                    Add a poster or notice and it will rotate in the carousel.
                    <div class="mt-4"><x-ui.button :href="route('announcements.create')" icon="bi-plus-lg">New announcement</x-ui.button></div>
                </x-ui.empty>
            @else
                <x-ui.table caption="Announcements">
                    <x-slot:head><th class="w-20">Poster</th><th>Announcement</th><th>Window</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></x-slot:head>
                    @foreach ($announcements as $announcement)
                        @php($status = $announcement->status())
                        <tr>
                            <td>
                                @if ($announcement->image_url)
                                    <img src="{{ $announcement->image_url }}" alt="" class="h-16 w-14 rounded-lg bg-subtle object-cover ring-1 ring-line">
                                @else
                                    <span class="grid h-16 w-14 place-items-center rounded-lg bg-subtle text-muted ring-1 ring-line"><i class="bi bi-image" aria-hidden="true"></i></span>
                                @endif
                            </td>
                            <td class="max-w-sm">
                                <p class="font-semibold text-ink">{{ $announcement->title }}</p>
                                @if ($announcement->body)<p class="line-clamp-2 text-xs text-muted">{{ $announcement->body }}</p>@endif
                                @if ($announcement->link_url)<p class="mt-1 truncate text-xs text-brand-700 dark:text-brand-300"><i class="bi bi-link-45deg" aria-hidden="true"></i> {{ $announcement->link_url }}</p>@endif
                            </td>
                            <td class="whitespace-nowrap text-xs text-muted">
                                <p>From {{ $announcement->published_at?->format('d M Y, H:i') ?? 'now' }}</p>
                                <p>Until {{ $announcement->expires_at?->format('d M Y, H:i') ?? 'no end date' }}</p>
                            </td>
                            <td class="tabular-nums">{{ $announcement->sort_order }}</td>
                            <td>
                                <x-ui.badge dot :tone="match ($status) { 'Live' => 'emerald', 'Scheduled' => 'brand', 'Expired' => 'amber', default => 'slate' }">{{ $status }}</x-ui.badge>
                            </td>
                            <td>
                                <div class="flex justify-end gap-1.5">
                                    <form method="POST" action="{{ route('announcements.toggle', $announcement) }}">
                                        @csrf @method('PATCH')
                                        <x-ui.button variant="ghost" size="sm" :icon="$announcement->is_active ? 'bi-eye-slash' : 'bi-eye'" :aria-label="($announcement->is_active ? 'Hide ' : 'Show ').$announcement->title">{{ $announcement->is_active ? 'Hide' : 'Show' }}</x-ui.button>
                                    </form>
                                    <x-ui.button variant="outline" size="sm" icon="bi-pencil" :href="route('announcements.edit', $announcement)" :aria-label="'Edit '.$announcement->title">Edit</x-ui.button>
                                    <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" data-confirm="Delete this announcement?" data-confirm-text="The poster image is removed too." data-confirm-tone="danger">
                                        @csrf @method('DELETE')
                                        <x-ui.button variant="ghost" size="sm" icon="bi-trash" class="!text-rose-700 dark:!text-rose-300" :aria-label="'Delete '.$announcement->title"><span class="sr-only">Delete</span></x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>

        <div>
            <h3 class="mb-3 text-sm font-semibold text-muted">Live preview</h3>
            <x-announcement-carousel :announcements="\App\Models\Announcement::query()->visible()->get()" />
        </div>
    </div>
@endsection
