<?php
$a_values = [
    'what'          => 'What Is Being Deleted, constant',
    'name'          => 'Something to help one know which one, e.g. myConstant',
    'extra_message' => 'an extra message',
    'submit_value'  => 'value that is being submitted by button, defaults to delete',
    'form_action'   => 'the url, e.g. /manger/config/constants/',
    'cancel_action' => 'the url for canceling the delete if different from form action',
    'btn_value'     => 'What the Button says, e.g. Constants',
    'hidden_name'   => 'primary id name, e.g., const_id',
    'hidden_value'  => 'primary id, e.g. 1',
];
$a_options = [
    'tpl'         => 'verify_delete',
    'page_prefix' => 'site_',
    'location'    => '/manager/ (or an id like 12)',
    'a_message'   => ['type' => 'success', 'message' => 'Success'],
    'fallback'    => 'render'
];

