<?php
$search_params = [
    'order_by'        => 'id ASC', // column name(s) to sort by e.g. id [ASC || DESC][, column_name]
    'search_type'     => 'AND',    // 'AND' | 'OR'
    'limit_to'        => 50,       // limit the number of records to return
    'starting_from'   => 0,        // which record number to start a limited return
    'comparison_type' => '=',      // what kind of comparison to use for ALL WHEREs
    'where_exists'    => false,    // add where exists to the search
    'a_fields'        => [         // array list of field names to return
        'field_name',
        'nother_field_name'
    ]
];
