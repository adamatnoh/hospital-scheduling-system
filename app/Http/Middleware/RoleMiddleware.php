<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Restrict a route to one or more roles stored on the user.
     *
     * Usage: ->middleware('role:scheduler,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $role = $user?->role instanceof Role ? $user->role->value : $user?->role;

        if ($user === null || ! in_array($role, $roles, true)) {
            return redirect()->route('unauthorized');
        }

        return $next($request);
    }
}
