<?php
// THIS IS ADMIN MANAGE STAFF ACCOUNT CONTROLLER
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function manageStaff (Request $request)
    {
        $staffs = User::query()
            ->select('id', 'role', 'name', 'email', 'department')
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->get();

        return view('manageStaff', compact('staffs'));
    }

    public function updateStaff(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'in:Orthopedics,Emergency'],
            'role' => ['required', 'string', 'in:regular,scheduler'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->input('user_id'))],
        ]);

        $staff = User::query()->where('role', '!=', 'admin')->findOrFail($data['user_id']);
        $staff->update([
            'name' => $data['title'],
            'department' => $data['department'],
            'role' => $data['role'],
            'email' => $data['email'],
        ]);

        return redirect()->route('manageStaff');
    }

    public function getStaff($id)
    {
        $staff = User::query()
            ->select('id', 'role', 'name', 'email', 'department')
            ->where('role', '!=', 'admin')
            ->findOrFail($id);

        return response()->json($staff);
    }

    public function deleteStaff(Request $request, $id)
    {
        $staff = User::query()->where('role', '!=', 'admin')->find($id);
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
            'department' => ['required', 'string', 'in:Orthopedics,Emergency'],
            'role' => ['required', 'string', 'in:regular,scheduler'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $staff = User::create([
            'name' => $data['name'],
            'department' => $data['department'],
            'role' => $data['role'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json($staff->only(['id', 'name', 'email', 'role', 'department']));
    }
}
