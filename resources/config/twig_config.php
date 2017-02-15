<?php
$library_path  = APPS_PATH . '/Ritc/Library/resources/templates';
if (!defined('LIB_TWIG_PREFIX')) {
    define('LIB_TWIG_PREFIX', 'lib_');
}
return array(
    'default_path'     => $library_path,
    'additional_paths' => array(
        $library_path . '/tests'    => LIB_TWIG_PREFIX . 'tests',
        $library_path . '/snippets' => LIB_TWIG_PREFIX . 'snippets',
        $library_path . '/pages'    => LIB_TWIG_PREFIX . 'pages',
        $library_path . '/elements' => LIB_TWIG_PREFIX . 'elements',
        $library_path . '/default'  => LIB_TWIG_PREFIX . 'default',
        $library_path . '/forms'    => LIB_TWIG_PREFIX . 'forms',
    ),
    'environment_options' => array(
        'cache'       => SRC_PATH . '/twig_cache',
        'auto_reload' => true,
        'debug'       => true
    )
);
