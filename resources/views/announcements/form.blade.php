@extends('layouts.hss')
@section('heading', $announcement->exists ? 'Edit announcement' : 'New announcement')
@section('content')
    <x-ui.page-header :title="$announcement->exists ? 'Edit announcement' : 'New announcement'" icon="bi-megaphone"
        subtitle="Shown in the dashboard and landing page carousel while it is active and inside its window.">
        <x-slot:actions>
            <x-ui.button variant="outline" :href="route('announcements.index')" icon="bi-arrow-left">Back</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $announcement->exists ? route('announcements.update', $announcement) : route('announcements.store') }}"
          class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]"
          x-data="{ preview: @js($announcement->image_url), pick(e) { const f = e.target.files[0]; if (f) this.preview = URL.createObjectURL(f) } }">
        @csrf
        @if ($announcement->exists) @method('PUT') @endif

        <x-ui.card title="Content" icon="bi-card-text">
            <div class="grid gap-5">
                <x-ui.field label="Title" for="title" required>
                    <input id="title" name="title" maxlength="150" required class="hss-input" value="{{ old('title', $announcement->title) }}" @error('title') aria-invalid="true" @enderror>
                </x-ui.field>
                <x-ui.field label="Message" for="body" name="body" hint="Optional. Shown next to the poster.">
                    <textarea id="body" name="body" rows="5" class="hss-input" @error('body') aria-invalid="true" @enderror>{{ old('body', $announcement->body) }}</textarea>
                </x-ui.field>
                <div class="grid gap-5 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                    <x-ui.field label="Link URL" for="link_url" hint="Optional, e.g. a registration form.">
                        <input id="link_url" name="link_url" type="url" maxlength="500" class="hss-input" placeholder="https://" value="{{ old('link_url', $announcement->link_url) }}" @error('link_url') aria-invalid="true" @enderror>
                    </x-ui.field>
                    <x-ui.field label="Link label" for="link_label">
                        <input id="link_label" name="link_label" maxlength="60" class="hss-input" placeholder="Read more" value="{{ old('link_label', $announcement->link_label) }}">
                    </x-ui.field>
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-ui.field label="Publish from" for="published_at">
                        <input id="published_at" name="published_at" type="datetime-local" class="hss-input" value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}">
                    </x-ui.field>
                    <x-ui.field label="Expires" for="expires_at" hint="Leave empty to keep it up.">
                        <input id="expires_at" name="expires_at" type="datetime-local" class="hss-input" value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}" @error('expires_at') aria-invalid="true" @enderror>
                    </x-ui.field>
                    <x-ui.field label="Sort order" for="sort_order" hint="Lower shows first.">
                        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="hss-input" value="{{ old('sort_order', $announcement->sort_order ?? 0) }}">
                    </x-ui.field>
                </div>
                <label class="flex items-center gap-3 rounded-xl border border-line bg-subtle p-3 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="hss-check" @checked(old('is_active', $announcement->is_active ?? true))>
                    <span><span class="font-semibold text-ink">Active</span><span class="block text-xs text-muted">Untick to hide it without deleting.</span></span>
                </label>
            </div>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card title="Poster image" icon="bi-image">
                <div class="relative grid aspect-[4/5] place-items-center overflow-hidden rounded-xl bg-navy-950 ring-1 ring-line">
                    <template x-if="preview">
                        <img :src="preview" alt="Selected poster preview" class="h-full w-full object-contain">
                    </template>
                    <div x-show="! preview" class="text-center text-navy-300">
                        <i class="bi bi-image text-4xl" aria-hidden="true"></i>
                        <p class="mt-2 text-xs">JPG, PNG or WebP up to 5 MB</p>
                    </div>
                </div>
                <x-ui.field label="{{ $announcement->exists ? 'Replace image' : 'Upload image' }}" for="image" class="mt-4" :required="! $announcement->exists">
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @change="pick" @if (! $announcement->exists) required @endif
                           class="block w-full text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/40 dark:file:text-brand-200">
                </x-ui.field>
            </x-ui.card>
            <div class="flex justify-end gap-2">
                <x-ui.button variant="outline" :href="route('announcements.index')">Cancel</x-ui.button>
                <x-ui.button icon="bi-check-lg">{{ $announcement->exists ? 'Save changes' : 'Publish announcement' }}</x-ui.button>
            </div>
        </div>
    </form>
@endsection
