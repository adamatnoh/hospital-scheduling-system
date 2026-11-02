<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Services\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private AuditRecorder $audit) {}

    public function index(): View
    {
        return view('announcements.index', [
            'announcements' => Announcement::query()->orderBy('sort_order')->orderByDesc('id')->get(),
            'live' => Announcement::query()->visible()->count(),
        ]);
    }

    public function create(): View
    {
        return view('announcements.form', ['announcement' => new Announcement(['is_active' => true, 'published_at' => now()])]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = $request->file('image')->store('announcements', 'public');
        $data['created_by'] = $request->user()->id;

        $announcement = Announcement::query()->create($data);
        $this->audit->record('announcement.created', $announcement, ['title' => $announcement->title]);

        return redirect()->route('announcements.index')->with('status', 'Announcement published.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('announcements.form', ['announcement' => $announcement]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $old = $announcement->image_path;
            $data['image_path'] = $request->file('image')->store('announcements', 'public');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }

        $announcement->update($data);
        $this->audit->record('announcement.updated', $announcement, ['title' => $announcement->title]);

        return redirect()->route('announcements.index')->with('status', 'Announcement updated.');
    }

    public function toggle(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);
        $this->audit->record('announcement.'.($announcement->is_active ? 'shown' : 'hidden'), $announcement);

        return back()->with('status', $announcement->is_active ? 'Announcement is visible again.' : 'Announcement hidden.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }
        $this->audit->record('announcement.deleted', $announcement, ['title' => $announcement->title]);
        $announcement->delete();

        return redirect()->route('announcements.index')->with('status', 'Announcement deleted.');
    }
}
