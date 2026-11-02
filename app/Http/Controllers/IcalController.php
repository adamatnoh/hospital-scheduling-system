<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\IcalFeed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class IcalController extends Controller
{
    public function download(Request $request, IcalFeed $feed): Response
    {
        $user = $request->user();
        if ($user->calendar_token === null) {
            $user->forceFill(['calendar_token' => Str::random(48)])->save();
        }

        return response($feed->forUser($user), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="hss-'.$user->id.'.ics"',
        ]);
    }

    public function show(string $token, IcalFeed $feed): Response
    {
        $user = User::query()->where('calendar_token', $token)->firstOrFail();

        return response($feed->forUser($user), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }
}
