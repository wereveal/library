<?php
return [
    'read' => [
        'good_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'empty_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'bad_values' => [
            'test_values' => [],
            'expected_results' => false
        ]
    ],
    'create' => [
        'missing_values' => [
            'test_values' => [],
            'expected_results' => false
        ],
        'bad_values' => [
            'test_values' => [],
            'expected_results' => false
        ],
        'good_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'duplicate_values' => [
            'test_values' => [],
            'expected_results' => false
        ],
    ],
    'readById' => [
        'good_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'empty_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'bad_values' => [
            'test_values' => [],
            'expected_results' => false
        ]
    ],
    'readByName' => [
        'good_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'empty_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'bad_values' => [
            'test_values' => [],
            'expected_results' => false
        ]
    ],
    'update' => [
        'immutable_on' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'immutable_off' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'missing_values' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'isValidGroupId' => [
        'valid_id' => [
            'test_value' => 1,
            'expected_results' => true
        ],
        'invalid_id' => [
            'test_value' => 999,
            'expected_results' => false
        ]
    ],
    'delete' => [
        'immutable_on' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'missing_id' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'bad_id' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'immutable_off' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ],
    'deleteWithRelated' => [
        'immutable_on' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'missing_id' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'bad_id' => [
            'test_values' => [],
            'expected_results' => []
        ],
        'immutable_off' => [
            'test_values' => [],
            'expected_results' => []
        ]
    ]
];
