<?php
$twig_library = APP_PATH . '/Library/resources/templates';
return array(
    'default_path'        => $twig_library,
    'additional_paths'    => array(
        'default'  => $twig_library . '/default',
        'elements' => $twig_library . '/elements',
        'forms'    => $twig_library . '/forms',
        'main'     => $twig_library . '/main',
        'pages'    => $twig_library . '/pages',
        'snippets' => $twig_library . '/snippets',
        'tests'    => $twig_library . '/test',
    ),
    'environment_options' => array(
        'cache' => APP_PATH . '/twig_cache',
    )
);
