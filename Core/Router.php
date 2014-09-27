<?php
/**
 *  @brief Determines the controller and method to use based on URI.
 *  @file Router.php
 *  @ingroup ritc_library core
 *  @namespace Ritc/Library/Core
 *  @class Router
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0ß
 *  @date 2014-09-25 18:12:44
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0ß - initial attempt to make this - 09/25/2014 wer
**/
namespace Ritc\Library\Core;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Core\DbModel;

class Router extends Base
{
    protected $o_elog;
    protected $private_properties;
    private $o_db;
    private $uri;

    public function __construct(DbModel $o_db)
    {
        $this->setPrivateProperties();
        $this->uri = removeUriArgs();
        $this->o_db = $o_db;
    }

    public function removeUriArgs($request_uri = '')
    {
        if ($request_uri == '') {
            $request_uri = $_SERVER["REQUEST_URI"];
        }
        if (strpos($request_uri, "?") !== false) {
            return substr($request_uri, 0, strpos($request_uri, "?"));
        }
        else {
            return $request_uri;
        }
    }

    public function getRoute()
    {
        $db_prefix = $this->o_db->getDbPrefix();
        $a_uri = [':uri_path' => $this->uri];
        $sql = "SELECT controller, method, action FROM {$db_prefix}routes WHERE uri_path LIKE :uri_path";
        $a_results = $this->o_db->search($sql, $a_uri);
        if ($a_results !== false && count($a_results) > 0) {
            return $a_results[0];
        }
        else {
            return ['controller' => 'Main', 'method' => '', 'action' => ''];
        }
    }
}
