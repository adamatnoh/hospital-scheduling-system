<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\IcalFeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CalendarFeedController extends Controller
{
    /**
     * The subscribable feed. Calendar apps cannot sign in, so the secret token
     * in the URL is the only credential and the route runs without a session.
     */
    public function show(Request $request, string $token, IcalFeed $feed): Response
    {
        $user = User::findByCalendarFeedToken($token);
        abort_if($user === null, 404);

        return $this->calendar($request, $feed->forUser($user), 'inline', 'hss.ics');
    }

    /**
     * The /ical/{token} links handed out before the feed moved. A reset clears them.
     */
    public function legacy(Request $request, string $token, IcalFeed $feed): Response
    {
        $user = User::query()->whereNotNull('calendar_token')->where('calendar_token', $token)->first();
        abort_if($user === null, 404);

        return $this->calendar($request, $feed->forUser($user), 'inline', 'hss.ics');
    }

    public function subscribe(Request $request): View
    {
        $feedUrl = route('calendar.feed', ['token' => $request->user()->calendarFeedToken()]);
        $webcalUrl = (string) preg_replace('#^https?://#', 'webcal://', $feedUrl);

        return view('calendar.subscribe', [
            'feedUrl' => $feedUrl,
            'webcalUrl' => $webcalUrl,
            'googleUrl' => 'https://calendar.google.com/calendar/r?cid='.rawurlencode($webcalUrl),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->user()->resetCalendarFeedToken();

        return redirect()->route('calendar.subscribe')
            ->with('status', 'New calendar link created. The old link has stopped working.');
    }

    /**
     * A one-off .ics file for apps that cannot subscribe.
     */
    public function download(Request $request, IcalFeed $feed): Response
    {
        $user = $request->user();

        return $this->calendar($request, $feed->forUser($user), 'attachment', 'hss-'.Str::slug($user->name).'.ics');
    }

    private function calendar(Request $request, string $body, string $disposition, string $filename): Response
    {
        $response = response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);

        // The URL is a secret, so shared caches must not keep it; clients may for an hour.
        $response->setPrivate();
        $response->setMaxAge(3600);
        $response->setEtag(md5($body));
        $response->isNotModified($request);

        return $response;
    }
}
