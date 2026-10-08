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
  | the admin filters; the exteriors are also the columns of the lists and
  | the apartment export. Also the offer's texts and pictures: the building
  | as the offer mail names it, and the example photos on the offer page
  | (public/assets/img; none: the block is left out). Keyed by estates.domain.
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

        // Offer mail: «Sie haben sich für eine Wohnung in … interessiert.»
        'mail_building' => 'unserem Neubau «Eglistrasse»',

        // Offer page: example photos (columns of 12)
        'photos' => [
          ['src' => '/assets/img/aporta-eglistrasse-wohnraum.jpg', 'width' => 1016, 'height' => 718, 'span' => 12],
          ['src' => '/assets/img/aporta-eglistrasse-nasszellen.jpg', 'width' => 1000, 'height' => 1415, 'span' => 6],
          ['src' => '/assets/img/aporta-eglistrasse-treppenhaus.jpg', 'width' => 1000, 'height' => 1415, 'span' => 6],
          ['src' => '/assets/img/aporta-eglistrasse-gartenblick.jpg', 'width' => 1016, 'height' => 718, 'span' => 12],
        ],
      ],
    ],

    'kornhaus-roetelstrasse' => [

      'url' => env('ESTATE_KORNHAUS_ROETELSTRASSE_URL', 'https://kornhaus-roetelstrasse.aporta-stiftung.ch'),

      'settings' => [

        // Available apartment states
        'states' => [1, 2, 3, 5],

        // Available rent filter options
        'rent_steps' => [
          'lt:1500' => 'bis 1500',
          '1500:2000' => '1500 - 2000',
          '2000:2500' => '2000 - 2500',
          'gt:2500' => 'ab 2500',
        ],

        // Available exteriors
        'exteriors' => ['balcony' => 'Balkon', 'loggia' => 'Loggia', 'patio' => 'Sitzplatz'],

        // Offer mail: «Sie haben sich für eine Wohnung in … interessiert.»
        'mail_building' => 'unserer Liegenschaft «Kornhaus-/Rötelstrasse»',

        // Offer page: example photos (columns of 12); none yet
        'photos' => [],
      ],
    ],
  ],
];
