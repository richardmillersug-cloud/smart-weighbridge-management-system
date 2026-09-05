<?php

/**
 * Prepare CI environment for `php artisan native:build`.
 *
 * - Creates .env + APP_KEY (NativePHP cleanEnvFile requires it)
 * - Pre-patches electron package.json then runs npm install so the
 *   subsequent npm ci inside native:build succeeds
 */

$appRoot = dirname(__DIR__, 2);
chdir($appRoot);

if (! file_exists('.env')) {
    copy('.env.example', '.env');
    passthru('php artisan key:generate --force', $exitCode);
    if ($exitCode !== 0) {
        exit($exitCode);
    }
}

foreach ([
    'storage/certs',
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require $appRoot.'/vendor/autoload.php';
$app = require $appRoot.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$electronPath = is_file($appRoot.'/nativephp/electron/package.json')
    ? $appRoot.'/nativephp/electron'
    : $appRoot.'/vendor/nativephp/desktop/resources/electron';

if (! is_file($electronPath.'/package.json')) {
    fwrite(STDERR, "NativePHP electron project not found. Run php artisan native:install first.\n");
    exit(1);
}

$path = $electronPath.'/package.json';
$pkg = json_decode(file_get_contents($path), true);
$pkg['name'] = (string) str(config('app.name'))->slug();
$pkg['version'] = config('nativephp.version');
$pkg['description'] = config('nativephp.description');
$pkg['author'] = config('nativephp.author');
$pkg['homepage'] = config('nativephp.website');
file_put_contents($path, json_encode($pkg, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

chdir($electronPath);
passthru('npm install', $exitCode);

exit($exitCode);
