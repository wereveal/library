<?php
/**
 * @brief     Common functions used by controllers.
 * @ingroup   lib_traits
 * @file      ControllerTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-04-11 08:18:42
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2016-04-11 wer
 * @todo ControllerTraits.php - refactor other controllers to use this.
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;

/**
 * Trait ControllerTraits.
 * @class   ControllerTraits
 * @package Ritc\Library\Traits
 */
trait ControllerTraits
{
    use LogitTraits;

    /** @var  array */
    protected $a_post;
    /** @var  array */
    protected $a_router_parts;
    /** @var  DbModel */
    protected $o_db;
    /** @var  Di */
    protected $o_di;
    /** @var  Router */
    protected $o_router;
    /** @var  Session */
    protected $o_session;

    protected function setupController(Di $o_di)
    {
        $this->setObjects($o_di);
        $this->setProperties();
    }

    protected function setObjects(Di $o_di)
    {
        $this->o_di      = $o_di;
        $this->o_db      = $o_di->get('db');
        $this->o_router  = $o_di->get('router');
        $this->o_session = $o_di->get('session');
        if (defined(DEVELOPER_MODE) && DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
        }
    }

    protected function setProperties()
    {
        $a_router_parts       = $this->o_router->getRouteParts();
        $this->a_post         = $a_router_parts['post'];
        $this->a_router_parts = $a_router_parts;
    }
}
