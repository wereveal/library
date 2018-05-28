<?php
$a_read_params = [
    'table_name',      // The name of the table - this one is required
    'a_fields',        // The fields to return ['id', 'name', 'is_alive' as 'is_dead']
    'a_search_for',    // What to search for    ['id' => 3]
    'a_allowed_keys',  // Upon which fields are allowed to be searched
    'return_format',   // assoc, num, both - defaults to assoc
    'order_by',        // The order to return  'is_alive DESC, name ASC'
    'search_type',     // Either 'AND' | 'OR'
    'limit_to',        // Limit the number of records to return
    'starting_from',   // Which record number to start a limited return
    'comparison_type', // What kind of comparison operator to use for ALL WHEREs
    'where_exists',    // Either true or false, If WHERE exists, do not add to returned string
    'select_distinct'  // Either true or false Add DISTINCT to the SELECT
];
