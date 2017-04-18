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
    - config
      - A string specifying the twig config file to be used, e.g. twig_config.php
      - An array
        - a list of config files to use
            [
                [
                    'name'      => 'twig_config.php',
                    'namespace' => 'Ritc\Library'
                ],
                [
                    'name'      => 'twig_config.php',
                    'namespace' => 'Ritc\FtpAdmin'
                ],
            ]
        - or an array formatted as needed by the __construct method.
            [
                'default_path'      => '/Ritc/Library/resources/templates',
                'additional_paths'  => [
                    '/Ritc/Library/resources/templates/default' => 'lib_default',
                    '/Ritc/Library/resources/templates/pages'   => 'lib_pages',
                    '/Ritc/Library/resources/templates/stuff'   => 'lib_stuff',
                ],
                'environment_options' => [
                    'cache'       => SRC_PATH . '/twig_cache',
                    'auto_reload' => true,
                    'debug'       => true
            ]
    - name
      - namespace to find the config file, when config is a filename
      - name to give the instance, when config is an array
    - use_main_twig
      - Only used withn config is an array of config files to use.
      - true will start the config using the main site config and then include others
      - false will only use the configs specified to create the twig environment
