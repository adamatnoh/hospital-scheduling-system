<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;

class ExpireAnnouncements extends Command
{
    protected $signature = 'hss:expire-announcements';

    protected $description = 'Switch off announcements whose expiry time has passed';

    public function handle(): int
    {
        $count = Announcement::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['is_active' => false]);

        $this->info('Expired '.$count.' announcement(s).');

        return self::SUCCESS;
    }
}
