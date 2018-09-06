<?php
$a_values = [
    'what'          => 'What Is Being Deleted, constant',
    'name'          => 'Something to help one know which one, e.g. myConstant',
    'extra_message' => 'an extra message',
    'submit_value'  => 'value that is being submitted by button, defaults to delete',
    'form_action'   => 'the url, e.g. /manager/config/constants/',
    'cancel_action' => 'the url for canceling the delete if different from form action',
    'btn_value'     => 'What the Button says, e.g. Constants',
    'hidden_name'   => 'primary id name, e.g., const_id',
    'hidden_value'  => 'primary id, e.g. 1',
];
$a_options = [
    'location'    => '/manager/ (or an id like 12)', // if not provided defaults to $a_values['form_action']
    'tpl'         => 'verify_delete', // if not lib_prefix ~ pages/verify_delete.twig, page_prefix should be set too
    'page_prefix' => 'site_', // if not lib_
    'a_message'   => ['type' => 'success', 'message' => 'Success'],
    'fallback'    => 'render' // if something goes wrong, which method to fallback
];

