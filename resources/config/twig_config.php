<?php
$library_twig = APP_PATH . '/Library/resources/templates';
return array(
    'default_path'        => $library_twig,
    'additional_paths'    => array(
        $library_twig . '/default'  => 'default',
        $library_twig . '/elements' => 'elements',
        $library_twig . '/forms'    => 'forms',
        $library_twig . '/main'     => 'main',
        $library_twig . '/pages'    => 'pages',
        $library_twig . '/snippets' => 'snippets',
        $library_twig . '/tests'    => 'tests',
    ),
    'environment_options' => array(
        'cache' => APP_PATH . '/twig_cache',
    )
);
