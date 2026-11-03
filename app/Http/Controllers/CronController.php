<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs the scheduled maintenance tasks from a Vercel Cron Job.
 *
 * Serverless hosts have no long-running `schedule:work` process, and the
 * Hobby plan only allows one daily cron, so the tasks that matter are run
 * explicitly once a day (00:00 UTC = 08:00 Asia/Kuala_Lumpur).
 */
class CronController extends Controller
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private const DAILY = [
        'hss:remind-on-call' => [],
        'hss:expire-announcements' => [],
        'queue:prune-failed' => ['--hours' => 24 * 7],
        'auth:clear-resets' => [],
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.cron.secret');

        if ($secret === '' || ! hash_equals('Bearer '.$secret, (string) $request->header('Authorization'))) {
            abort(401);
        }

        $commands = self::DAILY;

        if (now()->isSunday()) {
            $commands['hss:prune'] = ['--audit-days' => 365, '--notification-days' => 90];
        }

        $results = [];

        foreach ($commands as $command => $parameters) {
            try {
                $results[$command] = [
                    'exit' => Artisan::call($command, $parameters),
                    'output' => trim(Artisan::output()),
                ];
            } catch (Throwable $e) {
                report($e);
                $results[$command] = ['exit' => 1, 'output' => $e->getMessage()];
            }
        }

        Log::info('Daily cron finished', $results);

        $failed = collect($results)->contains(fn (array $result) => $result['exit'] !== 0);

        return response()->json(['ok' => ! $failed, 'results' => $results], $failed ? 500 : 200);
    }
}
