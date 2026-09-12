<?php

/*
 * Verifies the PHP extensions Smart Weighbridge needs before the app starts.
 * Exit code 1 means php.ini must be edited on this PC.
 */

$required = [
    'ctype' => 'Laravel core',
    'curl' => 'HTTP calls and cloud sync',
    'dom' => 'ticket and report rendering',
    'fileinfo' => 'file storage',
    'filter' => 'Laravel core',
    'mbstring' => 'Laravel core',
    'openssl' => 'encryption, MySQL TLS, cloud sync',
    'pdo_mysql' => 'local MySQL database',
    'session' => 'login sessions',
    'tokenizer' => 'Laravel core',
    'xml' => 'Laravel core',
];

$missing = array_keys(array_filter(
    $required,
    static fn (string $reason, string $extension): bool => ! extension_loaded($extension),
    ARRAY_FILTER_USE_BOTH
));

if ($missing === []) {
    exit(0);
}

$ini = php_ini_loaded_file();

fwrite(STDERR, PHP_EOL.'[ERROR] PHP on this PC is missing extensions Smart Weighbridge needs:'.PHP_EOL.PHP_EOL);

foreach ($missing as $extension) {
    fwrite(STDERR, sprintf('  - %-12s (%s)%s', $extension, $required[$extension], PHP_EOL));
}

fwrite(STDERR, PHP_EOL.'Fix it once:'.PHP_EOL);

if ($ini === false) {
    fwrite(STDERR, '  1. PHP has no php.ini yet. Copy php.ini-production to php.ini in your PHP folder.'.PHP_EOL);
} else {
    fwrite(STDERR, '  1. Open '.$ini.' in Notepad (Run as administrator).'.PHP_EOL);
}

fwrite(STDERR, '  2. Set the extension folder:'.PHP_EOL);
fwrite(STDERR, '         extension_dir = "ext"'.PHP_EOL);
fwrite(STDERR, '  3. Add or uncomment these lines (remove any leading ";"):'.PHP_EOL);

foreach ($missing as $extension) {
    fwrite(STDERR, '         extension='.$extension.PHP_EOL);
}

fwrite(STDERR, '  4. Save, then start Smart Weighbridge again.'.PHP_EOL.PHP_EOL);

exit(1);
