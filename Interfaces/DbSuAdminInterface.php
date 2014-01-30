<?php
/**
 *  @brief Class used to set up database admin classes.
 *  @details Started because of the guilt trip that interfaces are all that.
 *  @file DbSuAdminInterface.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Interfaces
 *  @class DbSuAdminInterface
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-01-30 14:18:05
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 initial versioning 01/30/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

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
