<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        if ($app->configurationIsCached() || ! $app->environment('testing')) {
            throw new \RuntimeException('Test dibatalkan: hapus cache konfigurasi dan gunakan APP_ENV=testing.');
        }
        $connection = config('database.default');
        $database = (string) config('database.connections.'.$connection.'.database');
        if (filled(config('database.connections.'.$connection.'.url'))
            || ($connection === 'mysql' && ! str_ends_with($database, '_testing'))
            || ($connection === 'sqlite' && $database !== ':memory:')
            || ! in_array($connection, ['mysql', 'sqlite'], true)) {
            throw new \RuntimeException('Test dibatalkan: gunakan database MySQL khusus berakhiran _testing, atau SQLite :memory:. Jangan gunakan database skripsi/produksi.');
        }

        return $app;
    }
}
