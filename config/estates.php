<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Current estate
  |--------------------------------------------------------------------------
  |
  | Key of the estate this domain serves (matches estates.domain). The admin
  | uses it as its default estate.
  |
  */

  'current' => env('ESTATE_DOMAIN_KEY', 'eglistrasse'),

  /*
  |--------------------------------------------------------------------------
  | Estates
  |--------------------------------------------------------------------------
  |
  | Per estate: the public url (offer links in mails) and the settings for
  | the admin filters. Keyed by estates.domain.
  |
  */

  'estates' => [

    'eglistrasse' => [

      'url' => env('ESTATE_EGLISTRASSE_URL', 'https://eglistrasse.aporta-stiftung.ch'),

      'settings' => [

        // Available apartment states
        'states' => [1, 2, 3, 5],

        // Available rent filter options
        'rent_steps' => [
          'lt:1000' => 'bis 1000',
          '1000:1501' => '1000 - 1500',
          '1500:2000' => '1500 - 2000',
          'gt:2000' => 'ab 2000',
        ],

        // Available exteriors
        'exteriors' => ['terrace' => 'Terrasse', 'patio' => 'Sitzplatz', 'balcony' => 'Balkon'],
      ],
    ],
  ],
];
