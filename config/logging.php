<?php

// Only what differs from Laravel's defaults
return [

    'channels' => [
        // Errors also go to Slack (LOG_SLACK_WEBHOOK_URL)
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
            'ignore_exceptions' => false,
        ],
    ],

];
