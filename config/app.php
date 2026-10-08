<?php

use Illuminate\Support\ServiceProvider;

// Only what differs from Laravel's defaults
return [

    'locale' => 'de',

    // laravel-multidomain: its queue provider passes --domain on to workers
    'providers' => ServiceProvider::defaultProviders()->replace([
        \Illuminate\Queue\QueueServiceProvider::class => \Gecche\Multidomain\Queue\QueueServiceProvider::class,
    ])->toArray(),

];
