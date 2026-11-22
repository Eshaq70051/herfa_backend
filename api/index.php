<?php

// Vercel serverless read-only filesystem handling
$dirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
];
foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv('VERCEL=1');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');

$_SERVER['SCRIPT_NAME'] = '/index.php';

require __DIR__.'/../public/index.php';
