<?php

namespace App\View\Composers;

use App\Enums\RequestStatus;
use App\Models\ScheduleRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/**
 * Feeds the signed-in app shell: grouped navigation (also used by the
 * top-bar quick search), pending review count and the notification bell.
 */
class ShellComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $staff = $user->isScheduler() || $user->isAdmin();
        $pending = 0;
        if ($staff) {
            $pending = ScheduleRequest::query()
                ->where('status', RequestStatus::Pending)
                ->when(! $user->isAdmin(), fn ($q) => $q->where('department_id', $user->department_id))
                ->count();
        }

        $type = request()->route('type');
        $item = fn (string $label, string $icon, string $route, array $params = [], ?callable $active = null, $badge = null) => [
            'label' => $label,
            'icon' => $icon,
            'url' => route($route, $params),
            'active' => $active ? (bool) $active() : request()->routeIs($route),
            'badge' => $badge,
        ];
        $typed = fn (string $route, string $value) => fn () => request()->routeIs($route) && $type === $value;

        $sections = [
            ['title' => 'Overview', 'items' => [
                $item('Dashboard', 'bi-grid-1x2', 'dashboard'),
                $item('Your Schedule', 'bi-calendar-check', 'calendar.yourschedule'),
            ]],
            ['title' => 'Department rosters', 'items' => [
                $item('On Call', 'bi-moon-stars', 'calendar.department', ['on_call'], $typed('calendar.department', 'on_call')),
                $item('Ward', 'bi-hospital', 'calendar.department', ['ward'], $typed('calendar.department', 'ward')),
                $item('Shift', 'bi-clock', 'calendar.department', ['shift'], $typed('calendar.department', 'shift')),
            ]],
        ];

        if ($user->role?->isDoctor()) {
            $sections[] = ['title' => 'My requests', 'items' => [
                $item('Apply Leave', 'bi-calendar-x', 'calendar.leave'),
                $item('Apply On Call', 'bi-calendar-plus', 'calendar.oncall'),
                $item('Request a Swap', 'bi-arrow-left-right', 'swaps.create'),
                $item('Request History', 'bi-clock-history', 'calendar.history'),
            ]];
        }

        if ($staff) {
            $sections[] = ['title' => 'Scheduling', 'items' => [
                $item('Generate On Call', 'bi-magic', 'generate.show', ['on_call'], $typed('generate.show', 'on_call')),
                $item('Generate Ward', 'bi-diagram-3', 'generate.show', ['ward'], $typed('generate.show', 'ward')),
                $item('Generate Shift', 'bi-layout-three-columns', 'generate.show', ['shift'], $typed('generate.show', 'shift')),
                $item('Review Requests', 'bi-inbox', 'review', badge: $pending ?: null),
                $item('Review History', 'bi-archive', 'reviewHistory'),
            ]];
            if ($user->department_id && Route::has('rules.show')) {
                $sections[count($sections) - 1]['items'][] = $item('Roster Rules', 'bi-sliders', 'rules.show', [$user->department_id]);
            }
        }

        if ($user->isAdmin()) {
            $sections[] = ['title' => 'Administration', 'items' => [
                $item('Staff', 'bi-people', 'manageStaff'),
                $item('Public Holidays', 'bi-calendar-heart', 'holidays.index'),
                $item('Announcements', 'bi-megaphone', 'announcements.index', active: fn () => request()->routeIs('announcements.*')),
                $item('Audit Log', 'bi-journal-text', 'audit.index'),
            ]];
        }

        $view->with([
            'navSections' => $sections,
            'navSearch' => collect($sections)->flatMap(fn ($s) => collect($s['items'])->map(fn ($i) => [
                'label' => $i['label'], 'section' => $s['title'], 'url' => $i['url'], 'icon' => $i['icon'],
            ]))->values(),
            'pendingReviewCount' => $pending,
            'unreadCount' => $user->unreadNotifications()->count(),
            'recentNotifications' => $user->notifications()->latest()->limit(6)->get(),
        ]);
    }
}
