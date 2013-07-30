<?php
namespace Wer\Framework\Library;

interface DbSuAdminInterface
{
    public function addUser($name, $password);
    public function dropUser($name);
    public function renameUser($old_name, $new_name);
    public function grantUser($a_privileges, $a_on, $to, $a_options);
    public function revokeUser($a_privileges, $a_on, $from);
    public function createDb($db_name, $a_options);
    public function deleteDb($db_name);
    public function createTable($table_name, $a_fields, $a_options);
    public function alterTable($table_name, $a_options);
    public function renameTable($table_name, $new_table_name);
    public function dropTable($table_name, $a_options);
    public function createView($view_name, $select_statement, $with);
}
