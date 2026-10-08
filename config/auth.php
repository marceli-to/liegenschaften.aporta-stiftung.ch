<?php

// Only what differs from Laravel's defaults
return [

    'passwords' => [
        'users' => [
            'provider' => 'users',
            // Table name from the 2014 migration
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

];
