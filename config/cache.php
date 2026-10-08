<?php

// Only what differs from Laravel's defaults
return [

    // No cache table; the file store as before
    'default' => env('CACHE_STORE', 'file'),

];
