<?php

// Production optimization script for Railway deployment

return [
    'post-install-cmd' => [
        '@php artisan clear-compiled',
        '@php artisan optimize',
        '@php artisan config:cache',
        '@php artisan route:cache',
        '@php artisan view:cache',
    ],
    'post-update-cmd' => [
        '@php artisan clear-compiled',
        '@php artisan optimize',
    ],
    'post-autoload-dump' => [
        'Illuminate\\Foundation\\ComposerScripts::postAutoloadDump',
        '@php artisan package:discover',
    ],
];
