<?php
// THIS IS ADMIN MANAGE STAFF ACCOUNT CONTROLLER
namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function manageStaff (Request $request)
    {
        $staffs = User::query()
            ->with('department')
            ->where('role', '!=', Role::Admin->value)
            ->orderBy('name')
            ->get();

        return view('manageStaff', compact('staffs'));
    }

    public function updateStaff(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'exists:departments,name'],
            'role' => ['required', 'string', 'in:regular,scheduler'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->input('user_id'))],
        ]);

        $staff = User::query()->where('role', '!=', Role::Admin->value)->findOrFail($data['user_id']);
        $staff->update([
            'name' => $data['title'],
            'department_id' => Department::query()->where('name', $data['department'])->value('id'),
            'role' => $data['role'],
            'email' => $data['email'],
        ]);

        return redirect()->route('manageStaff');
    }

    public function getStaff($id)
    {
        $staff = User::query()
            ->with('department:id,name')
            ->where('role', '!=', Role::Admin->value)
            ->findOrFail($id);

        return response()->json([
            'id' => $staff->id,
            'role' => $staff->role?->value,
            'name' => $staff->name,
            'email' => $staff->email,
            'department' => $staff->department?->name,
        ]);
    }

    public function deleteStaff(Request $request, $id)
    {
        $staff = User::query()->where('role', '!=', Role::Admin->value)->find($id);
        if (! $staff || $staff->id === Auth::id()) {
            return response()->json([
                'error' => 'Unable to locate the staff account',
            ], 404);
        }
        $staff->delete();

        return response()->json(['id' => $staff->id, 'name' => $staff->name]);
    }

    public function addStaff(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'department' => ['required', 'string', 'exists:departments,name'],
            'role' => ['required', 'string', 'in:regular,scheduler'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $staff = User::create([
            'name' => $data['name'],
            'department_id' => Department::query()->where('name', $data['department'])->value('id'),
            'role' => $data['role'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'id' => $staff->id,
            'name' => $staff->name,
            'email' => $staff->email,
            'role' => $staff->role?->value,
            'department' => $data['department'],
        ]);
    }
}
