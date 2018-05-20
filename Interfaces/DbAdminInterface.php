<?php
namespace Ritc\Library\Interfaces;

/**
 * Interface DbAdminInterface
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2014-01-30 14:18:05
 * ## Change Log
 * - v1.0.0 initial versioning 01/30/2014 wer
 */
interface DbAdminInterface
{
    public function showColumns($table_name);
    public function showTables();
    public function showViews();
}
