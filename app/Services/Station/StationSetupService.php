<?php

namespace App\Services\Station;

use App\Models\WeighbridgeStation;
use App\Support\StationSetupState;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PDO;
use Throwable;

class StationSetupService
{
    /**
     * @return array{php: array{ok: bool, version: string}, extensions: array{ok: bool, missing: list<string>}, mysql: array{ok: bool, host: string, port: int}}
     */
    public function prerequisites(): array
    {
        $required = ['pdo_mysql', 'mbstring', 'openssl', 'ctype', 'fileinfo', 'tokenizer', 'xml', 'curl'];
        $missing = array_values(array_filter($required, fn (string $ext): bool => ! extension_loaded($ext)));

        $host = (string) config('database.connections.mysql.host', '127.0.0.1');
        $port = (int) config('database.connections.mysql.port', 3306);

        return [
            'php' => [
                'ok' => version_compare(PHP_VERSION, '8.4.0', '>='),
                'version' => PHP_VERSION,
            ],
            'extensions' => [
                'ok' => $missing === [],
                'missing' => $missing,
            ],
            'mysql' => [
                'ok' => $this->portIsOpen($host, $port),
                'host' => $host,
                'port' => $port,
            ],
        ];
    }

    public function prepareEnvironment(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $template = base_path('installer/env/.env.station.example');
            $fallback = base_path('.env.example');
            File::copy(File::exists($template) ? $template : $fallback, $envPath);
        }

        if (! filled(env('APP_KEY'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{ok: bool, warnings: list<string>}
     */
    public function install(array $input, ?UploadedFile $cloudCa = null): array
    {
        $warnings = [];

        $this->prepareEnvironment();

        $values = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => (string) $input['db_host'],
            'DB_PORT' => (string) $input['db_port'],
            'DB_DATABASE' => (string) $input['db_database'],
            'DB_USERNAME' => (string) $input['db_username'],
            'DB_PASSWORD' => (string) $input['db_password'],
            'WEIGHBRIDGE_DRIVER' => 'xk3190',
            'WEIGHBRIDGE_COM_PORT' => strtoupper((string) $input['com_port']),
            'CLOUD_SYNC_ENABLED' => ! empty($input['cloud_sync_enabled']) ? 'true' : 'false',
            'DB_CLOUD_HOST' => (string) ($input['db_cloud_host'] ?? ''),
            'DB_CLOUD_PORT' => (string) ($input['db_cloud_port'] ?? '25060'),
            'DB_CLOUD_DATABASE' => (string) ($input['db_cloud_database'] ?? 'smart_weighbridge'),
            'DB_CLOUD_USERNAME' => (string) ($input['db_cloud_username'] ?? ''),
            'DB_CLOUD_PASSWORD' => (string) ($input['db_cloud_password'] ?? ''),
        ];

        if ($cloudCa !== null) {
            $certsDir = storage_path('certs');
            File::ensureDirectoryExists($certsDir);
            $caPath = $certsDir.DIRECTORY_SEPARATOR.'ca-certificate.crt';
            $cloudCa->move($certsDir, 'ca-certificate.crt');
            $values['DB_CLOUD_SSL_CA'] = $caPath;
        } elseif (filled($input['db_cloud_ssl_ca'] ?? null)) {
            $values['DB_CLOUD_SSL_CA'] = (string) $input['db_cloud_ssl_ca'];
        }

        $this->writeEnv($values);
        Artisan::call('config:clear');
        $this->applyRuntimeConfig($values);

        $this->createLocalDatabase($values);
        Artisan::call('migrate', ['--force' => true]);

        try {
            Artisan::call('db:seed', ['--force' => true]);
        } catch (Throwable $e) {
            if (! Schema::hasTable('users') || ! DB::table('users')->exists()) {
                throw $e;
            }

            $warnings[] = 'Database already had accounts; skipped re-seeding.';
        }

        try {
            Artisan::call('storage:link', ['--force' => true]);
        } catch (Throwable) {
            // Link may already exist.
        }

        WeighbridgeStation::query()
            ->where('is_default', true)
            ->update(['com_port' => $values['WEIGHBRIDGE_COM_PORT']]);

        if ($values['CLOUD_SYNC_ENABLED'] === 'true') {
            try {
                DB::purge('mysql_cloud');
                DB::connection('mysql_cloud')->getPdo();
                Artisan::call('migrate', ['--database' => 'mysql_cloud', '--force' => true]);
            } catch (Throwable $e) {
                $warnings[] = 'Local station is ready, but cloud sync could not connect yet: '.$e->getMessage();
            }
        }

        $this->writeEnv(['STATION_SETUP_COMPLETE' => 'true']);
        StationSetupState::markComplete();

        return ['ok' => true, 'warnings' => $warnings];
    }

    /**
     * @param  array<string, string>  $values
     */
    public function writeEnv(array $values): void
    {
        $path = base_path('.env');
        $content = File::exists($path) ? File::get($path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->quoteEnv($value);

            if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $content)) {
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $content) ?? $content;
            } else {
                $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            }
        }

        File::put($path, $content);
    }

    /**
     * @param  array<string, string>  $values
     */
    private function applyRuntimeConfig(array $values): void
    {
        foreach ($values as $key => $value) {
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        Config::set([
            'database.connections.mysql.host' => $values['DB_HOST'],
            'database.connections.mysql.port' => $values['DB_PORT'],
            'database.connections.mysql.database' => $values['DB_DATABASE'],
            'database.connections.mysql.username' => $values['DB_USERNAME'],
            'database.connections.mysql.password' => $values['DB_PASSWORD'],
            'database.connections.mysql_cloud.host' => $values['DB_CLOUD_HOST'],
            'database.connections.mysql_cloud.port' => $values['DB_CLOUD_PORT'],
            'database.connections.mysql_cloud.database' => $values['DB_CLOUD_DATABASE'],
            'database.connections.mysql_cloud.username' => $values['DB_CLOUD_USERNAME'],
            'database.connections.mysql_cloud.password' => $values['DB_CLOUD_PASSWORD'],
            'database.connections.mysql_cloud.options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => $values['DB_CLOUD_SSL_CA'] ?? env('DB_CLOUD_SSL_CA'),
            ]) : [],
            'cloud_sync.enabled' => $values['CLOUD_SYNC_ENABLED'] === 'true',
            'weighbridge.driver' => $values['WEIGHBRIDGE_DRIVER'],
            'weighbridge.serial.port' => $values['WEIGHBRIDGE_COM_PORT'],
        ]);

        DB::purge('mysql');
        DB::purge('mysql_cloud');
    }

    /**
     * @param  array<string, string>  $values
     */
    private function createLocalDatabase(array $values): void
    {
        $dsn = sprintf('mysql:host=%s;port=%s', $values['DB_HOST'], $values['DB_PORT']);
        $pdo = new PDO($dsn, $values['DB_USERNAME'], $values['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $database = $values['DB_DATABASE'];
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        DB::purge('mysql');
    }

    private function portIsOpen(string $host, int $port): bool
    {
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen($host, $port, $errno, $errstr, 1);

        if (! is_resource($socket)) {
            return false;
        }

        fclose($socket);

        return true;
    }

    private function quoteEnv(string $value): string
    {
        if ($value === '' || preg_match('/[\s#"\'\\\\$]/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }
}
