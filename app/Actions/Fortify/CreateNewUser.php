<?php

namespace App\Actions\Fortify;

use App\Enums\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'department' => ['required', 'string', 'exists:departments,name'],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'role' => Role::Regular,
            'email' => $input['email'],
            'department_id' => Department::query()->where('name', $input['department'])->value('id'),
            'password' => Hash::make($input['password']),
        ]);
    }
}
