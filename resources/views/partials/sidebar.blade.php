<aside class="hss-sidebar" :class="{ 'is-open': open }">
    <a href="{{ route('dashboard') }}" class="block border-b px-4 py-4">
        <span class="text-lg font-bold text-[#012970]">HSS</span>
        <span class="block text-xs text-slate-500">Hospital Scheduling System</span>
    </a>
    <nav class="py-3 text-sm" aria-label="Primary">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>

        <p class="px-4 pt-3 text-xs font-semibold uppercase text-slate-400">Department schedule</p>
        <a href="{{ route('calendar.department', 'on_call') }}" class="{{ request('type') === 'on_call' && request()->routeIs('calendar.department') ? 'active' : '' }}"><i class="bi bi-grid"></i><span>On Call Schedule</span></a>
        <a href="{{ route('calendar.department', 'ward') }}" class="{{ request('type') === 'ward' && request()->routeIs('calendar.department') ? 'active' : '' }}"><i class="bi bi-hospital"></i><span>Ward Schedule</span></a>
        <a href="{{ route('calendar.department', 'shift') }}" class="{{ request('type') === 'shift' && request()->routeIs('calendar.department') ? 'active' : '' }}"><i class="bi bi-person-workspace"></i><span>Shift Schedule</span></a>
        <a href="{{ route('calendar.yourschedule') }}" class="{{ request()->routeIs('calendar.yourschedule') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i><span>Your Schedule</span></a>

        @if (auth()->user()->role?->isDoctor())
            <p class="px-4 pt-3 text-xs font-semibold uppercase text-slate-400">Applications</p>
            <a href="{{ route('calendar.leave') }}" class="{{ request()->routeIs('calendar.leave') ? 'active' : '' }}"><i class="bi bi-calendar-x"></i><span>Apply Leave</span></a>
            <a href="{{ route('calendar.oncall') }}" class="{{ request()->routeIs('calendar.oncall') ? 'active' : '' }}"><i class="bi bi-moon-stars"></i><span>Apply On Call</span></a>
            <a href="{{ route('swaps.create') }}" class="{{ request()->routeIs('swaps.create') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i><span>Request a Swap</span></a>
            <a href="{{ route('calendar.history') }}" class="{{ request()->routeIs('calendar.history') ? 'active' : '' }}"><i class="bi bi-clock-history"></i><span>Your History</span></a>
        @endif

        @if (auth()->user()->isScheduler() || auth()->user()->isAdmin())
            <p class="px-4 pt-3 text-xs font-semibold uppercase text-slate-400">Create schedule</p>
            <a href="{{ route('generate.show', 'on_call') }}" class="{{ request('type') === 'on_call' && request()->routeIs('generate.show') ? 'active' : '' }}"><i class="bi bi-pencil"></i><span>On Call Schedule</span></a>
            <a href="{{ route('generate.show', 'ward') }}" class="{{ request('type') === 'ward' && request()->routeIs('generate.show') ? 'active' : '' }}"><i class="bi bi-pencil"></i><span>Ward Schedule</span></a>
            <a href="{{ route('generate.show', 'shift') }}" class="{{ request('type') === 'shift' && request()->routeIs('generate.show') ? 'active' : '' }}"><i class="bi bi-pencil"></i><span>Shift Schedule</span></a>
            <a href="{{ route('review') }}" class="{{ request()->routeIs('review') ? 'active' : '' }}"><i class="bi bi-check-circle"></i><span>Review Changes Requested</span></a>
            <a href="{{ route('reviewHistory') }}" class="{{ request()->routeIs('reviewHistory') ? 'active' : '' }}"><i class="bi bi-clock-history"></i><span>Changes Requested History</span></a>
        @endif

        @if (auth()->user()->isAdmin())
            <p class="px-4 pt-3 text-xs font-semibold uppercase text-slate-400">Administration</p>
            <a href="{{ route('manageStaff') }}" class="{{ request()->routeIs('manageStaff') ? 'active' : '' }}"><i class="bi bi-people"></i><span>Staff</span></a>
            <a href="{{ route('holidays.index') }}" class="{{ request()->routeIs('holidays.index') ? 'active' : '' }}"><i class="bi bi-calendar-heart"></i><span>Public Holidays</span></a>
            <a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.index') ? 'active' : '' }}"><i class="bi bi-journal-text"></i><span>Audit Log</span></a>
        @endif
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="border-t p-4">
        @csrf
        <button type="submit" class="text-sm text-[#012970]"><i class="bi bi-box-arrow-right"></i> Log out</button>
    </form>
</aside>
<div class="fixed inset-0 z-30 bg-slate-900/40 md:hidden" x-show="open" x-cloak @click="open = false"></div>
