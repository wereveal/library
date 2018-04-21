# Examples Page {#examplespage}

## Search Parameters {#searchparams}
a_search_parameters is an array with the following optional keys \sa readparams

    - 'order_by'        => 'id ASC'         -- column name(s) to sort by e.g. id [ASC || DESC][, column_name]
    - 'search_type'     => 'AND' | 'OR'
    - 'limit_to'        => ''               -- limit the number of records to return
    - 'starting_from'   => ''               -- which record number to start a limited return
    - 'comparison_type' => '='              -- what kind of comparison to use for ALL WHEREs
    - 'where_exists'    => false
    - 'a_fields'        => ['field_name']   -- array list of field names to return (not all methods may use this)

Not all parameters need to be in the array, if doesn't exist, the default setting will be used.

## Generic Create Parameters {#createparams}
An array used in the DbUtilityTraits::genericCreate() method with the following keys

    - a_required_keys => ['key_name', 'nother_key_name'] keys that need to have a value
    - a_field_names   => ['key_name', 'nother_key_name'] keys that are allowed to have a value
    - a_psql          => an array providing data to handle postgresql inserts and get the new id
        - table_name  => string
        - column_name => string

## Generic Read Parameters {#readparams}
An array used in the DbUtilityTraits::genericRead() method with the following keys

    - 'table_name'      The name of the table - this one is required
    - 'a_fields'        The fields to return ['id', 'name', 'is_alive'] or ['id' as 'id', 'name' as 'the_name', 'is_alive' as 'is_dead']
    - 'a_search_for'    What to search for    ['id' => 3]
    - 'a_allowed_keys'  Upon which fields are allowed to be searched
    - 'return_format'   assoc, num, both - defaults to assoc
    - 'order_by'        The order to return  'is_alive DESC, name ASC'
    - 'search_type'     Either 'AND' | 'OR'
    - 'limit_to'        Limit the number of records to return
    - 'starting_from'   Which record number to start a limited return
    - 'comparison_type' What kind of comparison operator to use for ALL WHEREs
    - 'where_exists'    Either true or false, If WHERE exists, do not add to returned string
    - 'select_distinct' Either true or false Add DISTINCT to the SELECT

## TwigFactory Parameters {#twigfactory}
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
      - param_two is the namespace for the config file. May be blank
        if the config file is the default, /src/config/twig_config.php

## Verify Delete values {#verifydelete}
### verifyDelete has two paramaters, a_values and a_options
#### a_values is an assoc array 
and provides the needed values for the twig template.
```
[
    'what'          => 'What Is Being Deleted, constant',
    'name'          => 'Something to help one know which one, e.g. myConstant',
    'extra_message' => '',
    'submit_value'  => 'value that is being submitted by button, defaults to delete',
    'form_action'   => 'the url, e.g. /manger/config/constants/',
    'cancel_action' => 'the url for canceling the delete if different from form action',
    'btn_value'     => 'What the Button says, e.g. Constants',
    'hidden_name'   => 'primary id name, e.g., const_id',
    'hidden_value'  => 'primary id, e.g. 1',
]
```
#### a_options is an assoc array
```
[
    'tpl'         => 'verify_delete',
    'page_prefix' => 'site_',
    'location'    => '/manager/' || 12
    'a_message'   => [],
    'fallback'    => 'render',
]

- tpl is the twig template name, will default to 'verify_delete'
- page_prefix is the twig prefix to use for the template defaults to page_prefix
- location is the page url to use to determine twig values. 
- a_message is a message
- fallback is the fallback method to use if something is wrong.

if location is given, tpl and page prefix will be ignored.
if location and tpl and page_prefix are empty route uri will be used.
if tpl is only provided, page prefix will be determined by route uri 
 
```
