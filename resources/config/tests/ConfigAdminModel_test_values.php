<?php
return [
    'makeValidName' => [
        'test1' => [
            'test_value'       => '<a href="http://go.to.bad.place/">my name</a>',
            'expected_results' => 'MY_NAME'
        ],
        'test2' => [
            'test_value'       => 'My Name 123',
            'expected_results' => 'MY_NAME'
        ],
        'test3' => [
            'test_value'       => 'My&Name#--23',
            'expected_results' => 'MY_NAME'
        ],
        'test4' => [
            'test_value'       => '',
            'expected_results' => ''
        ]
    ],
    'read' => [
        'test1' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test2' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test3' => [
            'test_value'       => '',
            'expected_results' => ''
        ]
    ],
    'create' => [
        'test1' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test2' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test3' => [
            'test_value'       => '',
            'expected_results' => ''
        ]
    ],
    'update' => [
        'test1' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test2' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test3' => [
            'test_value'       => '',
            'expected_results' => ''
        ]
    ],
    'delete' => [
        'test1' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test2' => [
            'test_value'       => '',
            'expected_results' => ''
        ],
        'test3' => [
            'test_value'       => '',
            'expected_results' => ''
        ]
    ]
];