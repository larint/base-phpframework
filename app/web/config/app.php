<?php

return [
    'default_controller'    => 'site',
    'default_action'        => 'index',
    '404_controller'        => 'error',
    '404_action'            => 'index',
    'db'            =>  [
        'mysql' => [
            'host' => 'localhost',
            'user' => 'root'
        ]
    ]
];