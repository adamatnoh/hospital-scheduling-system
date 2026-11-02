<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private AuditRecorder $audit) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('staff.index', [
            'staff' => User::query()->with('department')->where('role', '!=', 'admin')->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $staff = User::query()->create($request->safe()->only(['name', 'email', 'password', 'role', 'department_id']));
        $this->audit->record('staff.created', $staff, ['role' => $staff->role->value], $staff->department_id);

        return back()->with('status', 'Staff account created.');
    }

    public function update(UpdateStaffRequest $request, User $user): RedirectResponse
    {
        $user->update($request->safe()->only(['name', 'email', 'role', 'department_id']));
        $this->audit->record('staff.updated', $user, [], $user->department_id);

        return back()->with('status', 'Staff account updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $this->audit->record('staff.deleted', $user, ['email' => $user->email], $user->department_id);
        $user->delete();

        return back()->with('status', 'Staff account removed.');
    }
}
