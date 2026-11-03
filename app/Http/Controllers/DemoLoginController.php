<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * One-click sign-in for the seeded demo accounts, so visitors can try each
 * role without typing credentials. Only the three accounts below can be
 * reached, and only while DEMO_LOGIN is switched on.
 */
class DemoLoginController extends Controller
{
    /** @var array<string, string> */
    public const ACCOUNTS = [
        'admin' => 'admin@hss.test',
        'scheduler' => 'scheduler@hss.test',
        'doctor' => 'doctor@hss.test',
    ];

    public function __invoke(Request $request, string $role): RedirectResponse
    {
        abort_unless(config('hss.demo_login'), 404);

        $email = self::ACCOUNTS[$role] ?? abort(404);
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'That demo account is not available right now.',
            ]);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
