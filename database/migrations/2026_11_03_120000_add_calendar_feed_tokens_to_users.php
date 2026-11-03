<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The subscribable calendar feed is looked up by a SHA-256 hash of its
     * token, so the database never holds a usable link in plain text. The
     * token itself is kept encrypted (APP_KEY) so the owner can see it again.
     * Additive only: the old calendar_token column keeps working for the
     * code that is still deployed until it is removed in a later release.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('calendar_feed_hash', 64)->nullable()->unique();
            $table->text('calendar_feed_token')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['calendar_feed_hash']);
            $table->dropColumn(['calendar_feed_hash', 'calendar_feed_token']);
        });
    }
};
