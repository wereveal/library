<?php
$twig_library = APP_PATH . '/Library/resources/templates/twig';
return array(
    'default_path'        => $twig_library,
    'additional_paths'    => array(
        'tests'   => $twig_library . '/test',
        'default' => $twig_library . '/main'
    ),
    'environment_options' => array(
        'cache' => APP_PATH . '/twig_cache',
    )
);
