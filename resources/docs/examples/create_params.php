<?php
$a_create_params = [
    'a_required_keys' => [ // keys that need to have a value
        'key_name',
        'nother_key_name'
    ],
    'a_field_names' => [   // keys that are allowed to have a value
        'key_name',
        'nother_key_name'
    ],
    'a_psql' => [          // an array providing data to handle postgresql inserts and get the new id
        'table_name'  => $this->db_table,
        'column_name' => $this->primary_index_name
    ],
    'table_name'      => 'string',
    'column_name'     => 'string',
    'allow_pin'       => false // if true it allows the create to specify the primary index value
];
