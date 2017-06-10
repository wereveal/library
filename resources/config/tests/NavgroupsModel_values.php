<?php
return [
    'read' => [
        'empty_id' => [
            'test_value'       => '',
            'expected_results' => true
        ],
        'empty_name' => [
            'test_value'       => '',
            'expected_results' => true
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
    ],
    'update' => [
    ],
    'delete' => [
    ],
    'deleteWithMap' => [
    ],
    'readByName' => [
    ],
    'readIdByName' => [
    ],
    'retrieveDefaultId' => [
    ],
    'retrieveDefaultName' => [
    ]
];
