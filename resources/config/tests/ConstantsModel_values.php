<?php
return [
    'makeValidName' => [
        'tag' => [
            'test_value'       => '<a href="http://go.to.bad.place/">my name</a>',
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
                'const_immutable' => 1
            ],
            'expected_results' => true
        ],
        'bad_values' => [
            'test_values' => [
                'const_name'      => '',
                'const_value'     => '',
                'const_immutable' => 'o'
            ],
            'expected_results' => false
        ],
        'no_values' => [
            'test_values'      => [],
            'expected_results' => false
        ]
    ],
    'update' => [
    ],
    'delete' => [
    ]
];
