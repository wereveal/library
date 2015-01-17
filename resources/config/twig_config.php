<?php
$library_path  = SRC_PATH . '/Ritc/Library/resources/templates';
return array(
    'default_path'     => $library_path,
    'additional_paths' => array(
        $library_path . '/tests'    => 'tests',
        $library_path . '/snippets' => 'snippets',
        $library_path . '/pages'    => 'pages',
        $library_path . '/main'     => 'main',
        $library_path . '/elements' => 'elements',
        $library_path . '/default'  => 'default',
        $library_path . '/forms'    => 'forms',
    ),
    'environment_options' => array(
        'cache'       => APP_PATH . '/twig_cache',
        'auto_reload' => true,
        'debug'       => true
    )
);
