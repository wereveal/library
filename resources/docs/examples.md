# Examples Page {#examplespage}

## Search Paramaters {#searchparams}
a_search_parameters is an array with the following keys

    - 'order_by'        => 'id ASC'         -- column name(s) to sort by e.g. id [ASC || DESC][, column_name]
    - 'search_type'     => 'AND' | 'OR'
    - 'limit_to'        => ''               -- limit the number of records to return
    - 'starting_from'   => ''               -- which record number to start a limited return
    - 'comparison_type' => '='              -- what kind of comparison to use for ALL WHEREs
    - 'where_exists'    => false

Not all parameters need to be in the array, if doesn't exist, the default setting will be used.

