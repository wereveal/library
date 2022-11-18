<?php
namespace Ritc\Library;

return [
    'setDefaultTtl' => [
        'good' => [
            'test_value'       => CACHE_TTL + 5,
            'expected_results' => CACHE_TTL + 5
        ],
        'bad' => [
            'test_value'       => 'fred',
            'expected_results' => ''
        ],
        'empty' => [
            'test_value'       => '',
            'expected_results' => CACHE_TTL
        ]
    ],
    'getDefaultTtl' => [
       'good' => [
            'test_value'       => '',
            'expected_results' => CACHE_TTL
        ]
    ],
    'createFilePath' => [
        'string' => [
            'test_values' => ['key' => 'test',
                              'ttl' => CACHE_TTL],
            'expected_results' => ''
        ],
        'empty_string' => [
            'test_values'      => [],
            'expected_results' => ''
        ],
        'bad_path' => [
            'test_values'      => ['key' => 'bad_path/test', 'ttl' => ''],
            'expected_results' => ''
        ]
    ],
    'getCachePath' => [

    ],
    'getCacheType' => [],
    'set' => [
        'good' => [
            'test_values'      => ['key' => 'goodTest',
                                   'value' => 'success',
                                   'default' => 'failure'],
            'expected_results' => 'success'
        ],
        'bad' => [
            'test_value'       => ['key' => 'badTest',
                                   'value' => false,
                                   'default' => 'failure'],
            'expected_results' => 'failure'
        ],
        'empty' => [
            'test_value'       => ['key' => '',
                                   'value' => '',
                                   'default' => ''],
            'expected_results' => ''
        ]
    ],
    'get' => [
        'good' => [
            'test_values'      => ['key' => 'goodTest',
                                   'default' => 'failure'],
            'expected_results' => 'success'
        ],
        'bad' => [
            'test_value'       => ['key' => 'badTest',
                                   'default' => 'failure'],
            'expected_results' => 'failure'
        ],
        'empty' => [
            'test_value'       => ['key' => 'emptyTest',
                                   'default' => ''],
            'expected_results' => ''
        ]
    ],
    'setMultiple' => [],
    'getMultiple' => [],
    'getMultipleByPrefix' => [],
    'has' => [],
    'clearByKeyPrefix' => [],
    'cleanExpiredFiles' => [],
    'clear' => []
];