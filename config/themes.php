<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

return [
    'paths' => [
        'base' => base_path('themes'),
        'unzip_target_path' => base_path('storage/extensions/.tmp'),
        'backups' => base_path('storage/extensions/backups'),
        'themes' => base_path('themes'),
        'assets' => public_path('assets'),

        'generator' => [
            'assets' => [
                'path' => 'assets',
                'generate' => true,
                'in_multi' => false,
            ],
        ],
    ],

    'stubs' => [
        'path' => dirname(__DIR__).'/src/Commands/stubs',
        'files' => [
            'assets/js/app' => 'assets/js/app.js',
            'assets/sass/app' => 'assets/sass/app.scss',
            'assets/fresns.png' => 'assets/fresns.png',
            'theme.json' => 'theme.json',
        ],
        'gitkeep' => true,
    ],

    'manager' => [
        'default' => [
            'file' => base_path('fresns.json'),
        ],
    ],
];
