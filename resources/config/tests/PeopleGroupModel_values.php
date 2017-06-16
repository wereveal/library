<?php
return [
    'read' => [
        'empty_values' => [
            'test_values' => [],
            'expected_results' => true
        ],
        'bad_values' => [
            'test_values' => ['pgm_id' => 999],
            'expected_results' => false
        ],
        'bad_keys' => [
            'test_values' => ['fred' => 'fred'],
            'expected_results' => false
        ],
        'good_values_id' => [
            'test_values' => ['pgm_id' => 1],
            'expected_results' => true
        ],
        'good_values_group' => [
            'test_values' => ['group_id' => 1],
            'expected_results' => true
        ],
        'good_values_people' => [
            'test_values' => ['people_id' => 1],
            'expected_results' => true
        ]
    ],
    'create' => [
        /*
         * 'missing_value'
         * 'bad_value'
         * 'good_value'
         * 'good_value_array'
         */
    ],
    'update' => [
        /*
         * 'missing_value'
         * 'bad_value'
         * 'good_value'
         * 'good_value_array'
         */
    ],
    'delete' => [
        /*
         * 'missing_value'
         * 'bad_value'
         * 'good_value'
         * 'good_value_array'
         */
    ],
    'deleteByGroupId' => [
        /*
         * 'missing_value'
         * 'bad_value'
         * 'good_value'
         * 'good_value_array'
         */
    ],
    'deleteByPeopleId' => [
        /*
         * 'missing_value'
         * 'bad_value'
         * 'good_value'
         * 'good_value_array'
         */
    ],
    'readByGroupId' => [
        /*
         * 'missing_value'
         * 'bad_value'
         * 'good_value'
         * 'good_value_array'
         */
    ],
    'readByPeopleId' => [
        /*
         * 'missing_value'
         * 'bad_value'
         * 'good_value'
         * 'good_value_array'
         */
    ]
    /* Example
    'example' => [
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
    */
];
