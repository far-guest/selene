<?php

return [

    'route_prefix' => 'selene',
    'index_page' => [
        'type' => 'resource',
        'resource' => '',
    ],

    'middleware' => [
        'web',
        'auth',
    ],

    'ui' => [
        'external_help_url_generator' => function ($tree) {
            return false;
        },
    ],

    'resources' => [
        'mode' => 'auto', // or manual

        'auto' => app_path('Selene/Resources'),
        'manual' => [],
    ],

    'tools' => [
        'mode' => 'auto', // or manual

        'auto' => app_path('Selene/Tools'),
        'manual' => [],
    ],

    'views' => [
        'mode' => 'auto', // or manual

        'auto' => app_path('Selene/Views'),
        'manual' => [],
    ],

    'menu' => [
        \Selene\Menu\AllResources::make('منابع', 'fa fa-database'),
    ],
];
