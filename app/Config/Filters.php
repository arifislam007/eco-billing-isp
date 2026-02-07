<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'     => \CodeIgniter\Filters\CSRF::class,
        'toolbar'  => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot' => \CodeIgniter\Filters\Honeypot::class,
        'auth'     => \App\Filters\Auth::class,
        'customer' => \App\Filters\Customer::class,
    ];

    public array $filters = [
        'auth' => [
            'before' => [
                'admin/*',
            ],
        ],
        'customer' => [
            'before' => [
                'portal/*',
            ],
        ],
    ];
}
