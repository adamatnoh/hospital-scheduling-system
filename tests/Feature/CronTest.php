<?php

use Illuminate\Support\Facades\Artisan;

it('rejects cron calls without the secret', function () {
    config(['services.cron.secret' => 'test-secret']);

    $this->getJson('/api/cron/daily')->assertUnauthorized();
    $this->getJson('/api/cron/daily', ['Authorization' => 'Bearer wrong'])->assertUnauthorized();
});

it('rejects cron calls when no secret is configured', function () {
    config(['services.cron.secret' => null]);

    $this->getJson('/api/cron/daily', ['Authorization' => 'Bearer '])->assertUnauthorized();
});

it('runs the daily maintenance commands', function () {
    config(['services.cron.secret' => 'test-secret']);

    $this->getJson('/api/cron/daily', ['Authorization' => 'Bearer test-secret'])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('results.hss:remind-on-call.exit', 0)
        ->assertJsonPath('results.hss:expire-announcements.exit', 0);
});
