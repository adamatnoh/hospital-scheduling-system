<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // RefreshDatabase wipes whatever it connects to, so refuse to run
        // against anything but the in-memory SQLite database.
        $config = $app['config'];
        $connection = $config->get('database.connections.'.$config->get('database.default'), []);

        if (($connection['driver'] ?? null) !== 'sqlite'
            || ($connection['database'] ?? null) !== ':memory:'
            || filled($connection['url'] ?? null)) {
            throw new RuntimeException('Tests must run against in-memory SQLite. Unset DATABASE_URL / DB_URL before running them.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
