<?php

return [
    '__name' => 'api-profile-recovery',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/api-profile-recovery.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'https://iqbalfn.com/'
    ],
    '__files' => [
        'app/api-profile-recovery' => ['install','remove'],
        'modules/api-profile-recovery' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'api' => NULL
            ],
            [
                'profile' => NULL
            ],
            [
                'profile-auth' => NULL
            ],
            [
                'site-profile-recovery' => NULL
            ],
            [
                'lib-form' => NULL
            ],
            [
                'lib-otp' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'ApiProfileRecovery\\Controller' => [
                'type' => 'file',
                'base' => 'app/api-profile-recovery/controller'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'api' => [
            'apiProfileRecovery' => [
                'path' => [
                    'value' => '/pme/recovery'
                ],
                'handler' => 'ApiProfileRecovery\\Controller\\Recovery::recovery',
                'method' => 'POST'
            ],
            'apiProfileRecoveryReset' => [
                'path' => [
                    'value' => '/pme/recovery/reset/(:hash)',
                    'params'=> [
                        'hash' => 'any'
                    ]
                ],
                'handler' => 'ApiProfileRecovery\\Controller\\Recovery::reset',
                'method' => 'PUT'
            ],
            'apiProfileRecoveryResent' => [
                'path' => [
                    'value' => '/pme/recovery/resent/(:profile)/(:otp)',
                    'params'=> [
                        'profile' => 'number',
                        'otp' => 'number'
                    ]
                ],
                'handler' => 'ApiProfileRecovery\\Controller\\Recovery::resent',
                'method' => 'POST'
            ],
            'apiProfileRecoveryVerify' => [
                'path' => [
                    'value' => '/pme/recovery/verify/(:profile)/(:code)',
                    'params' => [
                        'profile' => 'number',
                        'code' => 'any'
                    ]
                ],
                'handler' => 'ApiProfileRecovery\\Controller\\Recovery::verify',
                'method' => 'GET'
            ]
        ]
    ],
    'libForm' => [
        'forms' => [
            'api.profile.recovery' => [
                'identity' => [
                    'label' => 'Identity',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE
                    ]
                ]
            ],
            'api.profile.recovery.reset' => [
                'password' => [
                    'label' => 'New Password',
                    'type' => 'password',
                    'rules' => [
                        'required' => true,
                        'empty' => false,
                        'length' => ['min' => 6]
                    ]
                ],
                're-password' => [
                    'label' => 'Retype Password',
                    'type' => 'password',
                    'rules' => [
                        'required' => true,
                        'empty' => false,
                        'equals_to' => 'password'
                    ]
                ]
            ],
            'api.profile.recovery.verify' => [
                'code' => [
                    'label' => 'Code',
                    'type' => 'text',
                    'rules' => [
                        'required' => TRUE,
                        'empty' => FALSE
                    ]
                ]
            ],
        ]
    ]
];