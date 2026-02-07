<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Database extends BaseConfig
{
    public string $default = 'isp_billing';
    public array $environments = [
        'development' => [
            'default' => [
                'DSN'      => '',
                'hostname' => 'localhost',
                'username' => 'billing',
                'password' => 'Billing123',
                'database' => 'isp_billing',
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => false,
                'DBDebug'  => true,
                'charset'  => 'utf8mb4',
                'DBCollat' => 'utf8mb4_unicode_ci',
                'swapPre'  => '',
                'encrypt'  => false,
                'compress' => false,
                'strictOn' => false,
                'failover' => [],
                'port'     => 3306,
            ],
            'radius' => [
                'DSN'      => '',
                'hostname' => 'localhost',
                'username' => 'billing',
                'password' => 'Billing123',
                'database' => 'radius',
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => false,
                'DBDebug'  => true,
                'charset'  => 'utf8mb4',
                'DBCollat' => 'utf8mb4_unicode_ci',
                'swapPre'  => '',
                'encrypt'  => false,
                'compress' => false,
                'strictOn' => false,
                'failover' => [],
                'port'     => 3306,
            ],
        ],
        'production' => [
            'default' => [
                'DSN'      => '',
                'hostname' => 'localhost',
                'username' => 'billing',
                'password' => 'Billing123',
                'database' => 'isp_billing',
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => false,
                'DBDebug'  => false,
                'charset'  => 'utf8mb4',
                'DBCollat' => 'utf8mb4_unicode_ci',
                'swapPre'  => '',
                'encrypt'  => false,
                'compress' => false,
                'strictOn' => false,
                'failover' => [],
                'port'     => 3306,
            ],
            'radius' => [
                'DSN'      => '',
                'hostname' => 'localhost',
                'username' => 'billing',
                'password' => 'Billing123',
                'database' => 'radius',
                'DBDriver' => 'MySQLi',
                'DBPrefix' => '',
                'pConnect' => false,
                'DBDebug'  => false,
                'charset'  => 'utf8mb4',
                'DBCollat' => 'utf8mb4_unicode_ci',
                'swapPre'  => '',
                'encrypt'  => false,
                'compress' => false,
                'strictOn' => false,
                'failover' => [],
                'port'     => 3306,
            ],
        ],
    ];
}
