<?php
/**
 * Interface DbAdminInterface
 * @package Ritc_Library
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface for manager controllers that does database stuff.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2014-01-30 14:18:05
 * @change_log
 * - v1.0.0 initial version                                 - 01/30/2014 wer
 */
interface DbAdminInterface
{
    /**
     * Gets the columns for the table then routes the data to the view.
     * @param string $table_name Required
     * @return string
     */
    public function showColumns($table_name = ''):string;

    /**
     * Gets the tables for the database then routes the data to the view.
     * @param string $database_name Required
     * @return string
     */
    public function showTables($database_name = ''):string;

    /**
     * Not sure what this does
     * @return string
     */
    public function showViews():string;
}
