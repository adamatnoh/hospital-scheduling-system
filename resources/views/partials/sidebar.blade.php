<aside id="hss-sidebar"
       class="hss-sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col transition-transform duration-200 ease-out lg:translate-x-0"
       :class="{ '!translate-x-0 shadow-2xl': open }" aria-label="Sidebar">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/5 px-5">
        <a href="{{ route('dashboard') }}" class="rounded-lg" aria-label="HSS dashboard">
            <x-ui.logo dark />
        </a>
        <button type="button" class="grid h-9 w-9 place-items-center rounded-lg text-navy-200 hover:bg-white/10 lg:hidden" @click="open = false" aria-label="Close menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto pb-4" aria-label="Primary">
        @foreach ($navSections as $section)
            <p class="nav-section">{{ $section['title'] }}</p>
            @foreach ($section['items'] as $link)
                <a href="{{ $link['url'] }}" class="nav-item" @if ($link['active']) aria-current="page" @endif>
                    <i class="bi {{ $link['icon'] }}" aria-hidden="true"></i>
                    <span class="flex-1">{{ $link['label'] }}</span>
                    @if ($link['badge'])
                        <span class="rounded-full bg-amber-400 px-2 py-0.5 text-[0.68rem] font-bold text-navy-950">{{ $link['badge'] }}<span class="sr-only"> pending</span></span>
                    @endif
                </a>
            @endforeach
        @endforeach
    </nav>

    <div class="border-t border-white/5 p-4">
        <div class="mb-3 rounded-xl bg-white/5 p-3">
            <p class="text-[0.68rem] font-semibold uppercase tracking-wider text-navy-300">Shift legend</p>
            <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-navy-100">
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-shift-morning"></span>Morning</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-shift-evening"></span>Evening</span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-shift-night ring-1 ring-white/30"></span>Night</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <x-ui.avatar :name="auth()->user()->name" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->displayName() }}</p>
                <p class="truncate text-xs text-navy-300">{{ auth()->user()->role?->label() }}{{ auth()->user()->department ? ' · '.auth()->user()->department->name : '' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="grid h-9 w-9 place-items-center rounded-lg text-navy-200 hover:bg-white/10 hover:text-white" aria-label="Log out" title="Log out">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
<div class="fixed inset-0 z-40 bg-navy-950/60 backdrop-blur-sm lg:hidden" x-show="open" x-transition.opacity x-cloak @click="open = false" aria-hidden="true"></div>
