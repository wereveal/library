<?php
# for TwigFactory::getTwig($param_one, $param_two) #
/**
getTwig has two parameters, param_one and param_two. Based on what
param_one is, a method will be called, getTwigByDb or getTwigByFile.
- for getTwigByDb
  - param_one is an instance of Ritc\Services\Di
  - param_two is a boolean, to use the twig cache or not
- for getTwigByFile
  - param_one can be
    - A string specifying the twig config file to be used, e.g. twig_config.php
    - An array of twig_config files, with instance name and use_default twig_config.php
      [
        'instance_name' => 'main',
        'use_default'   => true,
        'twig_files'    => [
          [
            'name'      => 'twig_config.php',
            'namespace' => 'MyNamespace\MyApp'
          ],
          [
            'name'      => 'twig_config.php',
            'namespace' => 'MyNamespace\MyOtherApp'
          ]
        ]
      ]
    - An array formatted as needed by the self::__construct method.
    [
      'default_path'      => '/Ritc/Library/resources/templates',
      'additional_paths'  => [
        '/Ritc/Library/resources/templates/themes' => 'lib_themes',
        '/Ritc/Library/resources/templates/pages'  => 'lib_pages',
        '/Ritc/Library/resources/templates/stuff'  => 'lib_stuff',
      ],
      'environment_options' => [
        'cache'       => SRC_PATH . '/twig_cache',
        'auto_reload' => true,
        'debug'       => true
      ]
    ]
    - param_two is the namespace for the config file. May be blank
      if the config file is the default, /src/config/twig_config.php
*/
