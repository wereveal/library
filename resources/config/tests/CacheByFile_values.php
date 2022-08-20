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
    'set' => [],
    'get' => [],
    'setMultiple' => [],
    'getMultiple' => [],
    'getMultipleByPrefix' => [],
    'has' => [],
    'clearByKeyPrefix' => [],
    'cleanExpiredFiles' => [],
    'clear' => []
];