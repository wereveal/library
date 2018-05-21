<?php
/**
 * Interface DbSuAdminInterface
 * @package RITC_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for the superuser admin controller.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2014-01-30 14:18:05
 * ## Change Log
 * - v1.0.0 initial version                                 - 01/30/2014 wer
 */
interface DbSuAdminInterface
{
    /**
     * Routes to model then to view.
     * @param $name
     * @param $password
     * @return mixed
     */
    public function addUser($name, $password);

    /**
     * Routes to model then to view.
     * @param $name
     * @return mixed
     */
    public function dropUser($name);

    /**
     * Routes to model then to view.
     * @param $old_name
     * @param $new_name
     * @return mixed
     */
    public function renameUser($old_name, $new_name);

    /**
     * Routes to model then to view.
     * @param $a_privileges
     * @param $a_on
     * @param $to
     * @param $a_options
     * @return mixed
     */
    public function grantUser($a_privileges, $a_on, $to, $a_options);

    /**
     * Routes to model then to view.
     * @param $a_privileges
     * @param $a_on
     * @param $from
     * @return mixed
     */
    public function revokeUser($a_privileges, $a_on, $from);

    /**
     * Routes to model then to view.
     * @param $db_name
     * @param $a_options
     * @return mixed
     */
    public function createDb($db_name, $a_options);

    /**
     * Routes to model then to view.
     * @param $db_name
     * @return mixed
     */
    public function deleteDb($db_name);

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $a_fields
     * @param $a_options
     * @return mixed
     */
    public function createTable($table_name, $a_fields, $a_options);

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $a_options
     * @return mixed
     */
    public function alterTable($table_name, $a_options);

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $new_table_name
     * @return mixed
     */
    public function renameTable($table_name, $new_table_name);

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $a_options
     * @return mixed
     */
    public function dropTable($table_name, $a_options);

    /**
     * Routes to model then to view.
     * @param $view_name
     * @param $select_statement
     * @param $with
     * @return mixed
     */
    public function createView($view_name, $select_statement, $with);
}
