<?php
/**
 * You should set the parts of Talentree you want to use. An example of how this configuration works
 */
return [
    'apiKey' => '!!YourApiKey!!',
    'apiRoot' => 'https://talentree.io/v1/', //only for internal use
    'apiOptions' => [
        'settings' => [
            25 => [
                'id' => 25,
                'icon' => 'fa fa-bookmark',
                'name' => 'Functierol',
                'items' => [
                    [
                        'id' => 25,
                        'name' => 'Functierol'
                    ]
                ]
            ],
            17 => [
                'id' => 17,
                'icon' => 'fa fa-bolt',
                'name' => 'Functiegebied',
                'items' => [
                    [
                        'id' => 17,
                        'name' => 'Functiegebied'
                    ]
                ]
            ],
            1738 => [
                'id' => 1738,
                'icon' => 'fa fa-briefcase',
                'name' => 'Kennisgebieden',
                'items' => [
                    [
                        'id' => 1738,
                        'name' => 'Kennisgebieden'
                    ]
                ]
            ],
            11 => [
                'id' => 11,
                'icon' => 'fa fa-cog',
                'name' => 'Vaardigheden',
                'items' => 'all-children'
            ],
            37 => [
                'id' => 37,
                'icon' => 'fa fa-flask',
                'name' => 'Methoden',
                'items' => [
                    [
                        'id' => 37,
                        'name' => 'Methoden'
                    ]
                ]
            ],
            604 => [
                'id' => 604,
                'icon' => 'fa fa-male',
                'name' => 'Persoonlijke eigenschappen',
                'items' => [
                    [
                        'id' => 604,
                        'name' => 'Persoonlijke eigenschappen'
                    ]
                ]
            ]
        ],
        'filter_settings' => [
            25 => 'Functierol',
            17 => 'Functiegebied',
            1738 => 'Kennisgebieden',
            11 => 'Vaardigheden',
            37 => 'Methoden',
        ]
    ]
];
