<?php
/**
 * Interface DbSuAdminInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for the superuser admin controller.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2021-11-29 17:11:51
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-29 wer
 * - v1.0.0 - initial version                                   - 01/30/2014 wer
 */
interface DbSuAdminInterface
{
    /**
     * Routes to model then to view.
     * @param $name
     * @param $password
     * @return mixed
     */
    public function addUser($name, $password): mixed;

    /**
     * Routes to model then to view.
     * @param $name
     * @return mixed
     */
    public function dropUser($name): mixed;

    /**
     * Routes to model then to view.
     * @param $old_name
     * @param $new_name
     * @return mixed
     */
    public function renameUser($old_name, $new_name): mixed;

    /**
     * Routes to model then to view.
     * @param $a_privileges
     * @param $a_on
     * @param $to
     * @param $a_options
     * @return mixed
     */
    public function grantUser($a_privileges, $a_on, $to, $a_options): mixed;

    /**
     * Routes to model then to view.
     * @param $a_privileges
     * @param $a_on
     * @param $from
     * @return mixed
     */
    public function revokeUser($a_privileges, $a_on, $from): mixed;

    /**
     * Routes to model then to view.
     * @param $db_name
     * @param $a_options
     * @return mixed
     */
    public function createDb($db_name, $a_options): mixed;

    /**
     * Routes to model then to view.
     * @param $db_name
     * @return mixed
     */
    public function deleteDb($db_name): mixed;

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $a_fields
     * @param $a_options
     * @return mixed
     */
    public function createTable($table_name, $a_fields, $a_options): mixed;

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $a_options
     * @return mixed
     */
    public function alterTable($table_name, $a_options): mixed;

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $new_table_name
     * @return mixed
     */
    public function renameTable($table_name, $new_table_name): mixed;

    /**
     * Routes to model then to view.
     * @param $table_name
     * @param $a_options
     * @return mixed
     */
    public function dropTable($table_name, $a_options): mixed;

    /**
     * Routes to model then to view.
     * @param $view_name
     * @param $select_statement
     * @param $with
     * @return mixed
     */
    public function createView($view_name, $select_statement, $with): mixed;
}
