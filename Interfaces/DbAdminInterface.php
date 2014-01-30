<?php
/**
 *  @brief Class used to set up database admin classes.
 *  @details Started because of the guilt trip that interfaces are all that.
 *  @file DbAdminInterface.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Interfaces
 *  @class DbAdminInterface
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2014-01-30 14:18:05
 *  @note A part of the RITC Library v5
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 initial versioning 01/30/2014 wer
 *  </pre>
**/
namespace Ritc\Library\Interfaces;

interface DbAdminInterface
{
    public function showColumns($table_name);
    public function showTables();
    public function showViews();
}
