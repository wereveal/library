<?php
/**
 * @brief     Common functions used by controllers.
 * @ingroup   lib_traits
 * @file      ControllerTraits.php
 * @namespace Ritc\Library\Traits
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2016-09-03 12:47:21
 * @note Change Log
 * - v1.0.0         - added one more commonly used property and out of alpha    - 2016-09-03 wer
 * - v1.0.0-alpha.1 - added a couple more commonly used properties and setters  - 2016-04-15 wer
 * - v1.0.0-alpha.0 - Initial version                                           - 2016-04-11 wer
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
    /** @var  string */
    protected $form_action;
    /** @var  string */
    protected $main_action;
    /** @var  DbModel */
    protected $o_db;
    /** @var  Di */
    protected $o_di;
    /** @var  Router */
    protected $o_router;
    /** @var  Session */
    protected $o_session;
    /** @var  string */
    protected $route_method;
    /** @var  string */
    protected $url_action_one;

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
        $this->o_elog    = $o_di->get('elog');
    }

    protected function setProperties()
    {
        $a_router_parts       = $this->o_router->getRouteParts();
        $this->a_router_parts = $a_router_parts;
        $this->a_post         = $a_router_parts['post'];
        $this->form_action    = $a_router_parts['form_action'];
        $this->route_method   = $a_router_parts['route_method'];
        $this->url_action_one = isset($a_router_parts['url_actions'][0])
            ? $a_router_parts['url_actions'][0]
            : '';
        if ($a_router_parts['route_action'] != '') {
            $this->main_action = $a_router_parts['route_action'];
        }
        elseif ($this->url_action_one != '') {
            $this->main_action = $this->url_action_one;
        }
        elseif ($this->form_action != '') {
            $this->main_action = $this->form_action;
        }
        else {
            $this->main_action = '';
        }
    }
}
