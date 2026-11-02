<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneRecords extends Command
{
    protected $signature = 'hss:prune
        {--audit-days=365 : Keep audit log entries for this many days}
        {--notification-days=90 : Keep read notifications for this many days}';

    protected $description = 'Delete old audit log entries and read notifications';

    public function handle(): int
    {
        $auditDays = max(30, (int) $this->option('audit-days'));
        $notificationDays = max(7, (int) $this->option('notification-days'));

        $audit = AuditLog::query()->where('created_at', '<', now()->subDays($auditDays))->delete();
        $notifications = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($notificationDays))
            ->delete();

        $this->info("Pruned {$audit} audit entr".($audit === 1 ? 'y' : 'ies')." older than {$auditDays} days and {$notifications} read notification(s) older than {$notificationDays} days.");

        return self::SUCCESS;
    }
}
