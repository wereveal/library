<?php
return [
    'read' => [
        'no_values' => [
            'test_value'       => '',
            'expected_results' => true
        ],
        'empty_id' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'empty_name' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'valid_id' => [
            'test_value'       => 1,
            'expected_results' => true
        ],
        'valid_name' => [
            'test_value'       => 'Main',
            'expected_results' => true
        ],
        'invalid_id' => [
            'test_value'       => 999,
            'expected_results' => false
        ],
        'invalid_name' => [
            'test_value'       => 'invalid_name',
            'expected_results' => false
        ]
    ],
    'create' => [
        'no_values' => [
            'test_values'      => [],
            'expected_results' => false
        ],
        'valid_values' => [
            'test_values'      => [
                'ng_name'      => 'Test',
                'ng_active'    => 1,
                'ng_default'   => 1,
                'ng_immutable' => 1
            ],
            'expected_results' => true
        ],
        'missing_required' => [
            'test_values'      => [
                'ng_active'    => 1,
                'ng_default'   => 1,
                'ng_immutable' => 1
            ],
            'expected_results' => false
        ]
    ],
    'update' => [
        'no_values' => [
            'test_values'      => [],
            'expected_results' => false
        ],
        'valid_values' => [
            'test_values'      => [
                'ng_id'        => 'Replace in script',
                'ng_name'      => 'Testing',
                'ng_active'    => 0,
                'ng_default'   => 0,
                'ng_immutable' => 0
            ],
            'expected_results' => true
        ],
        'bad_values' => [
            'test_values'      => [
                'ng_id'        => -1,
                'ng_name'      => '',
                'ng_active'    => false,
                'ng_default'   => 111,
                'ng_immutable' => 'a11'
            ],
            'expected_results' => false
        ],
        'missing_id' => [
            'test_values'      => [
                'ng_name'      => '',
                'ng_active'    => 0,
                'ng_default'   => 0,
                'ng_immutable' => 0
            ],
            'expected_results' => false
        ],
        'missing_name' => [
            'test_values'      => [
                'ng_id'        => -1,
                'ng_name'      => '',
                'ng_active'    => 0,
                'ng_default'   => 0,
                'ng_immutable' => 0
            ],
            'expected_results' => false
        ],
        'duplicate_name' => [
            'test_values'      => [
                'ng_id'        => -1,
                'ng_name'      => 'Main',
                'ng_active'    => 0,
                'ng_default'   => 0,
                'ng_immutable' => 0
            ],
            'expected_results' => false
        ]
    ],
    'delete' => [
        'real_delete' => [
            'test_values'      => [
                'ng_id' => -1
            ],
            'expected_results' => true
        ],
        'not_there' => [
            'test_values'      => [
                'ng_id' => -1
            ],
            'expected_results' => false
        ],
        'is_default' => [
            'test_values' => [
                'ng_id' => 0
            ],
            'expected_results' => false
        ]
    ],
    'deleteWithMap' => [
    ],
    'readById' => [
        'no_values' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'empty_id' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'invalid_id' => [
            'test_value'       => 99999999,
            'expected_results' => false
        ],
        'valid_id' => [
            'test_value'       => 1,
            'expected_results' => true
        ],
        'invalid_string_id' => [
            'test_value'       => 'invalid_id',
            'expected_results' => false
        ]

    ],
    'readByName' => [
        'no_values' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'empty_name' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'valid_name' => [
            'test_value'       => 'Main',
            'expected_results' => true
        ],
        'invalid_numeric_name' => [
            'test_value'       => 7,
            'expected_results' => false
        ],
        'invalid_string_name' => [
            'test_value'       => 'invalid_name',
            'expected_results' => false
        ]

    ],
    'readIdByName' => [
        'no_values' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'empty_name' => [
            'test_value'       => '',
            'expected_results' => false
        ],
        'valid_name' => [
            'test_value'       => 'Main',
            'expected_results' => true
        ],
        'invalid_numeric_name' => [
            'test_value'       => 7,
            'expected_results' => false
        ],
        'invalid_string_name' => [
            'test_value'       => 'invalid_name',
            'expected_results' => false
        ]
    ],
    'retrieveDefaultId' => [
        'test_value'       => '',
        'expected_results' => true
    ],
    'retrieveDefaultName' => [
        'test_value'       => '',
        'expected_results' => true
    ]
];
