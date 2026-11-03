@php($user = auth()->user())
<header class="hss-topbar sticky top-0 z-30">
    <div class="flex h-16 w-full items-center gap-3 px-4 sm:px-6 lg:px-8">
        <button type="button" class="grid h-10 w-10 place-items-center rounded-lg text-ink ring-1 ring-line hover:bg-subtle lg:hidden"
                @click="open = true" aria-controls="hss-sidebar" :aria-expanded="open.toString()" aria-label="Open menu">
            <i class="bi bi-list text-xl"></i>
        </button>

        <div class="min-w-0 flex-1 md:min-w-[10rem] md:flex-initial">
            <p class="truncate text-[0.7rem] font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ config('hss.hospital') }}</p>
            <h1 class="truncate text-base font-bold text-ink sm:text-lg">@yield('heading', 'Dashboard')</h1>
        </div>

        {{-- Quick search over every page this user can open (and staff, for admins). --}}
        <div class="relative ml-auto hidden w-full min-w-[11rem] max-w-[16rem] md:block xl:max-w-sm"
             x-data="{ q: '', show: false, active: 0, items: @js($navSearch),
                       get results() { const t = this.q.toLowerCase().trim(); if (! t) return this.items.slice(0, 6); return this.items.filter(i => (i.label + ' ' + i.section).toLowerCase().includes(t)).slice(0, 8) },
                       go(i) { const r = this.results[i]; if (r) window.location = r.url; @if ($user->isAdmin()) else if (this.q.trim()) window.location = @js(route('manageStaff')) + '?q=' + encodeURIComponent(this.q.trim()); @endif } }"
             @keydown.slash.window="if (! ['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName) && ! document.activeElement.isContentEditable) { $event.preventDefault(); $refs.search.focus() }"
             @click.outside="show = false">
            <label for="hss-search" class="sr-only">Search pages</label>
            <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted" aria-hidden="true"></i>
            <input id="hss-search" x-ref="search" type="search" x-model="q" autocomplete="off"
                   @focus="show = true" @input="show = true; active = 0"
                   @keydown.arrow-down.prevent="active = Math.min(active + 1, results.length - 1)"
                   @keydown.arrow-up.prevent="active = Math.max(active - 1, 0)"
                   @keydown.enter.prevent="go(active)" @keydown.escape="show = false; $el.blur()"
                   role="combobox" aria-autocomplete="list" aria-controls="hss-search-list" :aria-expanded="show.toString()"
                   placeholder="{{ $user->isAdmin() ? 'Search pages or staff…' : 'Search pages…' }}"
                   class="h-10 w-full rounded-xl border border-line bg-subtle pl-9 pr-10 text-sm text-ink placeholder:text-muted focus:border-brand-500 focus:bg-surface focus:outline-none focus:ring-4 focus:ring-brand-500/15">
            <kbd class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded border border-line bg-surface px-1.5 text-[0.65rem] font-semibold text-muted">/</kbd>
            <ul id="hss-search-list" role="listbox" x-show="show" x-cloak x-transition.opacity
                class="absolute inset-x-0 top-12 overflow-hidden rounded-xl border border-line bg-surface py-1 shadow-[var(--shadow-raised)]">
                <template x-for="(item, i) in results" :key="item.url">
                    <li role="option" :aria-selected="(i === active).toString()">
                        <a :href="item.url" @mouseenter="active = i" class="flex items-center gap-3 px-3 py-2 text-sm" :class="i === active ? 'bg-subtle' : ''">
                            <i class="bi text-brand-600 dark:text-brand-300" :class="item.icon" aria-hidden="true"></i>
                            <span class="flex-1 font-medium text-ink" x-text="item.label"></span>
                            <span class="text-xs text-muted" x-text="item.section"></span>
                        </a>
                    </li>
                </template>
                @if ($user->isAdmin())
                    <li x-show="q.trim()"><a :href="@js(route('manageStaff')) + '?q=' + encodeURIComponent(q.trim())" class="flex items-center gap-3 border-t border-line px-3 py-2 text-sm text-muted hover:bg-subtle"><i class="bi bi-person-lines-fill" aria-hidden="true"></i><span>Search staff for “<span x-text="q.trim()"></span>”</span></a></li>
                @else
                    <li x-show="results.length === 0" class="px-3 py-3 text-sm text-muted">No matching pages</li>
                @endif
            </ul>
        </div>

        <div class="ml-auto flex shrink-0 items-center gap-1.5 md:ml-3">
            <div class="hidden text-right xl:block" x-data="{ now: new Date(), init() { setInterval(() => this.now = new Date(), 30000) } }">
                <p class="text-sm font-semibold tabular-nums text-ink" x-text="now.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit', timeZone: @js(config('app.timezone')) })">{{ now()->format('h:i A') }}</p>
                <p class="text-xs text-muted" x-text="now.toLocaleDateString('en-MY', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', timeZone: @js(config('app.timezone')) })">{{ now()->format('D, j M Y') }}</p>
            </div>
            <span class="mx-2 hidden h-8 w-px bg-line xl:block" aria-hidden="true"></span>

            <button type="button" x-data="{ dark: document.documentElement.classList.contains('dark') }"
                    @click="dark = ! dark; document.documentElement.classList.toggle('dark', dark); localStorage.setItem('hss-theme', dark ? 'dark' : 'light')"
                    class="grid h-10 w-10 place-items-center rounded-lg text-muted hover:bg-subtle hover:text-ink" :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'" aria-label="Toggle dark mode">
                <i class="bi" :class="dark ? 'bi-sun' : 'bi-moon'" aria-hidden="true"></i>
            </button>

            {{-- Notifications --}}
            <div class="relative" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
                <button type="button" @click="menu = ! menu" :aria-expanded="menu.toString()" aria-haspopup="true"
                        class="relative grid h-10 w-10 place-items-center rounded-lg text-muted hover:bg-subtle hover:text-ink"
                        aria-label="Notifications{{ $unreadCount ? ', '.$unreadCount.' unread' : '' }}">
                    <i class="bi bi-bell text-lg" aria-hidden="true"></i>
                    @if ($unreadCount)
                        <span class="absolute right-1 top-1 grid min-w-[1.1rem] place-items-center rounded-full bg-rose-600 px-1 text-[0.62rem] font-bold leading-[1.1rem] text-white ring-2 ring-surface" data-unread-count>{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </button>
                <div x-show="menu" x-cloak x-transition.origin.top.right
                     class="absolute right-0 top-12 w-[22rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-line bg-surface shadow-[var(--shadow-raised)]">
                    <div class="flex items-center justify-between border-b border-line px-4 py-3">
                        <p class="font-semibold text-ink">Notifications</p>
                        @if ($unreadCount)
                            <form method="POST" action="{{ route('notifications.readAll') }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-brand-700 hover:underline dark:text-brand-300">Mark all as read</button>
                            </form>
                        @endif
                    </div>
                    <ul class="max-h-96 divide-y divide-line overflow-y-auto">
                        @forelse ($recentNotifications as $notification)
                            @php($data = $notification->data)
                            <li>
                                <a href="{{ $data['url'] ?? '#' }}" class="flex gap-3 px-4 py-3 hover:bg-subtle {{ $notification->read_at ? '' : 'bg-brand-50/60 dark:bg-brand-900/20' }}">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ str_contains($notification->type, 'OnCall') ? 'bg-violet-100 text-shift-night dark:bg-violet-900/40 dark:text-violet-200' : 'bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300' }}" aria-hidden="true">
                                        <i class="bi {{ str_contains($notification->type, 'OnCall') ? 'bi-moon-stars' : 'bi-envelope-check' }}"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-ink">{{ $data['title'] ?? (str_contains($notification->type, 'OnCall') ? 'On call reminder' : 'Request '.($data['status'] ?? 'updated')) }}</span>
                                        <span class="block text-xs text-muted">{{ $data['message'] ?? (isset($data['date']) ? 'On call '.$data['date'].' · '.($data['department'] ?? '') : ucfirst(str_replace('_', ' ', $data['type'] ?? 'request')).' from '.($data['start_date'] ?? '')) }}</span>
                                        <span class="mt-0.5 block text-[0.7rem] text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                    </span>
                                    @unless ($notification->read_at)<span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-brand-500" aria-label="Unread"></span>@endunless
                                </a>
                            </li>
                        @empty
                            <li><x-ui.empty icon="bi-bell-slash" title="You're all caught up" class="!py-8">Reminders and request decisions will show up here.</x-ui.empty></li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- User menu --}}
            <div class="relative" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
                <button type="button" @click="menu = ! menu" :aria-expanded="menu.toString()" aria-haspopup="true"
                        class="flex items-center gap-2 rounded-xl p-1 pr-2 hover:bg-subtle" aria-label="Account menu">
                    <x-ui.avatar :name="$user->name" />
                    <span class="hidden text-left sm:block">
                        <span class="block max-w-[10rem] truncate text-sm font-semibold text-ink">{{ $user->displayName() }}</span>
                        <span class="block text-xs text-muted">{{ $user->role?->label() }}</span>
                    </span>
                    <i class="bi bi-chevron-down hidden text-xs text-muted sm:block" aria-hidden="true"></i>
                </button>
                {{-- Sections are split by one divider each. Items show focus as a filled row:
                     the global outline would be clipped by overflow-hidden into stray lines. --}}
                @php($menuItem = 'flex w-full items-center gap-3 px-4 py-2 text-sm transition-colors hover:bg-subtle focus-visible:bg-subtle focus-visible:outline-none focus-visible:shadow-[inset_2px_0_0_var(--color-brand-500)]')
                <div x-show="menu" x-cloak x-transition.origin.top.right
                     class="absolute right-0 top-12 w-64 divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface shadow-[var(--shadow-raised)]">
                    <div class="px-4 py-3">
                        <p class="truncate text-sm font-semibold text-ink">{{ $user->displayName() }}</p>
                        <p class="truncate text-xs text-muted">{{ $user->email }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $user->role?->label() }}{{ $user->department ? ' · '.$user->department->name : '' }}</p>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('calendar.yourschedule') }}" class="{{ $menuItem }} text-ink"><i class="bi bi-calendar-check w-4 text-muted" aria-hidden="true"></i>Your schedule</a>
                        <a href="{{ route('calendar.subscribe') }}" class="{{ $menuItem }} text-ink"><i class="bi bi-calendar2-plus w-4 text-muted" aria-hidden="true"></i>Subscribe to calendar</a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="py-1">
                        @csrf
                        <button type="submit" class="{{ $menuItem }} text-rose-700 dark:text-rose-300"><i class="bi bi-box-arrow-right w-4" aria-hidden="true"></i>Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
