<?php
return [
    'read' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => [
                [
                    'people_id'       => 1,
                    'login_id'        => 'SuperAdmin',
                    'real_name'       => 'Super Admin',
                    'short_name'      => 'GSA',
                    'password'        => '$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW',
                    'description'     => 'The all powerful Admin',
                    'is_logged_in'    => 1,
                    'bad_login_count' => 0,
                    'bad_login_ts'    => 0,
                    'is_active'       => 1,
                    'is_immutable'    => 1,
                    'created_on'      => '2012-08-12 02:55:28'
                ],
                [
                    'people_id'       => 2,
                    'login_id'        => 'Admin',
                    'real_name'       => 'Admin',
                    'short_name'      => 'ADM',
                    'password'        => '$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW',
                    'description'     => 'Allowed to admin the backend.',
                    'is_logged_in'    => 1,
                    'bad_login_count' => 0,
                    'bad_login_ts'    => 0,
                    'is_active'       => 1,
                    'is_immutable'    => 1,
                    'created_on'      => '2015-09-04 13:15:55'
                ],
                [
                    'people_id'       => 3,
                    'login_id'        => 'FtpAdmin',
                    'real_name'       => 'Default Ftp Admin',
                    'short_name'      => 'DFA',
                    'password'        => '$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW',
                    'description'     => 'Allowed to crud the ftp logins. Can not access the site manager.',
                    'is_logged_in'    => 0,
                    'bad_login_count' => 0,
                    'bad_login_ts'    => 0,
                    'is_active'       => 1,
                    'is_immutable'    => 1,
                    'created_on'      => '2015-08-04 11:11:04'
                ]
            ]
        ],
        'test2' => [
            'test_values' => [['people_id' => 3],[]],
            'expected_results' => [
                [
                    'people_id'       => 3,
                    'login_id'        => 'FtpAdmin',
                    'real_name'       => 'Default Ftp Admin',
                    'short_name'      => 'DFA',
                    'password'        => '$2y$10$mAQZrjwnPDkfpdhmdfqxFuBJwY7w5HeCli2qs2H2Kg69w0MooNsJW',
                    'description'     => 'Allowed to crud the ftp logins. Can not access the site manager.',
                    'is_logged_in'    => 0,
                    'bad_login_count' => 0,
                    'bad_login_ts'    => 0,
                    'is_active'       => 1,
                    'is_immutable'    => 1,
                    'created_on'      => '2015-08-04 11:11:04'
                ]
            ]
        ],
        'test3' => [
            'test_values' => [['people_id' => 10],[]],
            'expected_results' => [false]
        ]
    ],
    'create' => [
        'test1' => [
            'test_values' => [
                'login_id'     => 'testPerson',
                'real_name'    => 'A Test Person',
                'short_name'   => '',
                'password'     => 'a test Person',
                'description'  => 'A Test Person',
                'is_logged_in' => 0,
                'is_active'    => 1,
                'is_immutable' => 0,
            ],
            'expected_results' => [
                [
                    'people_id'       => 4,
                    'login_id'        => 'testPerson',
                    'real_name'       => 'A Test Person',
                    'short_name'      => 'ATP',
                    'password'        => '',
                    'description'     => 'A Test Person',
                    'is_logged_in'    => 0,
                    'bad_login_count' => 0,
                    'bad_login_ts'    => 0,
                    'is_active'       => 1,
                    'is_immutable'    => 0
                ]
            ]
        ],
        'test2' => [
            'test_values' => [
                'login_id'  => 'testPersonAgain',
                'real_name' => 'A Test Person Again',
                'password'  => 'A Test Person Again'
            ],
            'expected_results' => [
                [
                    'people_id'       => 5,
                    'login_id'        => 'testPersonAgain',
                    'real_name'       => 'A Test Person Again',
                    'short_name'      => 'ATPA',
                    'password'        => '',
                    'description'     => 'A Test Person Again',
                    'is_logged_in'    => 0,
                    'bad_login_count' => 0,
                    'bad_login_ts'    => 0,
                    'is_active'       => 1,
                    'is_immutable'    => 0
                ]
            ]
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => [false]
        ]
    ],
    'update' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'delete' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'getPeopleId' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'incrementBadLoginCount' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'incrementBadLoginTimestamp' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'readPeopleRecord' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'resetBadLoginCount' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'resetBadLoginTimestamp' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'setBadLoginTimestamp' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'updatePassword' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'updateActive' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'readInfo' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'savePerson' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'deletePerson' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'isId' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'makeGroupIdArray' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'makePgmArray' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'getErrorMessage' => [
        'test1' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test2' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'test3' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ]
];
