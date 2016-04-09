<?php
$library_path  = SRC_PATH . '/Ritc/Library/resources/templates';
return array(
    'default_path'     => $library_path,
    'additional_paths' => array(
        $library_path . '/tests'    => 'lib_tests',
        $library_path . '/snippets' => 'lib_snippets',
        $library_path . '/pages'    => 'lib_pages',
        $library_path . '/elements' => 'lib_elements',
        $library_path . '/default'  => 'lib_default',
        $library_path . '/forms'    => 'lib_forms',
    ),
    'environment_options' => array(
        'cache'       => APP_PATH . '/twig_cache',
        'auto_reload' => true,
        'debug'       => true
    )
);
