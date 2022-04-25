<?php
return [
    'makeValidName' => [
        'tag' => [
            'test_value'       => '<a href="https:///go.to.bad.place/">my name</a>',
            'expected_results' => 'MY_NAME'
        ],
        'spaces' => [
            'test_value'       => 'My Name 123',
            'expected_results' => 'MY_NAME'
        ],
        'bad_characters' => [
            'test_value'       => 'My &Name#--23',
            'expected_results' => 'MY_NAME'
        ],
        'mixed_case' => [
            'test_value'       => 'mY_nAmE',
            'expected_results' => 'MY_NAME'
        ],
        'empty' => [
            'test_value'       => '',
            'expected_results' => ''
        ]
    ],
    'read' => [
        'good_name' => [
            'test_values'      => ['const_name' => 'DISPLAY_DATE_FORMAT'],
            'expected_results' => true
        ],
        'bad_name' => [
            'test_values'      => ['const_name' => 'BAD_NAME'],
            'expected_results' => false
        ],
        'missing_name' => [
            'test_values'      => [],
            'expected_results' => true
        ],
        'good_id' => [
            'test_values'      => ['const_id' => 1],
            'expected_results' => true
        ],
        'bad_id' => [
            'test_values'      => ['const_id' => 'fred'],
            'expected_results' => false
        ]
    ],
    'create' => [
        'good_values' => [
            'test_values' => [
                'const_name'      => 'FRED',
                'const_value'     => 'Fred Flinstone',
                'const_immutable' => 'true'
            ],
            'expected_results' => true
        ],
        'duplicate_values' => [
            'test_values' => [
                'const_name'      => 'FRED',
                'const_value'     => 'Fred Flinstone',
                'const_immutable' => 'true'
            ],
            'expected_results' => false
        ],
        'bad_values' => [
            'test_values' => [
                'const_name'      => '',
                'const_value'     => '',
                'const_immutable' => 'o'
            ],
            'expected_results' => false
        ],
        'empty_values' => [
            'test_values'      => [],
            'expected_results' => false
        ],
        'no_values' => [
            'test_values'      => '',
            'expected_results' => false
        ]
    ],
    'update' => [
        'no_values' => [
            'test_values'      => [],
            'expected_results' => false
        ],
        'const_immutable_alpha' => [
            'test_values' => [
                'const_name'      => 'FRED',
                'const_immutable' => 'will this work?'
            ],
            'expected_results' => false
        ],
        'name_immutable' => [
            'test_values' => [
                'const_name'      => 'PHREDRIK',
                'const_value'     => '',
            ],
            'expected_results' => false
        ],
        'immutable_change_false' => [
            'test_values' => [
                'const_name'      => 'phred',
                'const_value'     => 'phred phlinstone',
                'const_immutable' => 'false'
            ],
            'expected_results' => true
        ],
        'name_value_change' => [
            'test_values' => [
                'const_name'      => 'PHREDRICK',
                'const_value'     => 'Phredrick Flinstone'
            ],
            'expected_results' => true
        ],
        'immutable_change_true' => [
            'test_values' => [
                'const_immutable' => 'true'
            ],
            'expected_results' => true
        ]
    ],
    'delete' => [
        'still_immutable' => [
            'test_value'       => 999,
            'expected_results' => false
        ],
        'invalid_id' => [
            'test_value'       => 999,
            'expected_results' => false
        ],
        'no_values' => [
            'test_value'       => 999,
            'expected_results' => false
        ],
        'not_immutable' => [
            'test_value'       => 999,
            'expected_results' => true
        ]
    ]
];
