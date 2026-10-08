<?php

use Illuminate\Support\Str;

// Only what differs from Laravel's defaults
return [

    // No sessions table; the file driver as before (.env sets cookie)
    'driver' => env('SESSION_DRIVER', 'file'),

    // Keep the cookie name, so nobody is logged out by the upgrade
    'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),

];
