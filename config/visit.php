<?php

declare(strict_types=1);

use LaravelInteraction\Visit\Visit;

return [
    'load_migrations' => true,
    'models' => [
        'user' => \App\User::class,
        'visit' => Visit::class,
    ],
    'table_names' => [
        'visits' => 'visits',
    ],
    'column_names' => [
        'user_foreign_key' => 'user_id',
    ],
    /*
    |--------------------------------------------------------------------------
    | Default Divisors For Humans
    |--------------------------------------------------------------------------
    |
    | Divisors: Thousand, Million, Billion, Trillion, Quadrillion, Quintillion
    | Shorthands: "K", "M", "B", "T", "Qa", "Qi"
    |
    */
    'divisors' => [
        1000 ** 0 => '',
        1000 ** 1 => 'K',
        1000 ** 2 => 'M',
        1000 ** 3 => 'B',
        1000 ** 4 => 'T',
        1000 ** 5 => 'Qa',
        1000 ** 6 => 'Qi',
    ],
];
