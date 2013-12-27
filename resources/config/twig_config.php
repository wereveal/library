<?php
$library_twig = APP_PATH . '/Library/resources/templates';
return array(
    'default_path'        => $library_twig,
    'additional_paths'    => array(
        $library_twig . '/default'  => 'library_default',
        $library_twig . '/elements' => 'library_elements',
        $library_twig . '/forms'    => 'library_forms',
        $library_twig . '/main'     => 'library_main',
        $library_twig . '/pages'    => 'library_pages',
        $library_twig . '/snippets' => 'library_snippets',
        $library_twig . '/tests'    => 'library_tests',
        $example_twig . '/default'  => 'default',
        $example_twig . '/elements' => 'elements',
    ),
    'environment_options' => array(
        'cache' => APP_PATH . '/twig_cache',
    )
);
