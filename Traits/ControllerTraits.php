<?php
/**
 * Trait ControllerTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\CacheHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Services\Session;

/**
 * Common functions used by controllers.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.4.0
 * @date    2018-06-13 16:14:22
 * @change_log
 * - v1.4.0         - added the cache property for injection                    - 2018-06-13 wer
 * - v1.3.0         - added a commonly used property route_class                - 2017-05-10 wer
 * - v1.2.0         - added a commonly used property                            - 2017-02-06 wer
 * - v1.1.0         - added a commonly used property                            - 2016-09-09 wer
 * - v1.0.0         - added one more commonly used property and out of alpha    - 2016-09-03 wer
 * - v1.0.0-alpha.1 - added a couple more commonly used properties and setters  - 2016-04-15 wer
 * - v1.0.0-alpha.0 - Initial version                                           - 2016-04-11 wer
 */
trait ControllerTraits
{
    /** @var  array */
    protected $a_post = [];
    /** @var  array */
    protected $a_router_parts = [];
    /** @var array */
    protected $a_url_actions = [];
    /** @var string $cache_type */
    protected $cache_type;
    /** @var  string */
    protected $form_action = '';
    /** @var  string */
    protected $main_action = '';
    /** @var CacheHelper $o_cache */
    protected $o_cache;
    /** @var  DbModel */
    protected $o_db;
    /** @var  Di */
    protected $o_di;
    /** @var  Router */
    protected $o_router;
    /** @var  Session */
    protected $o_session;
    /** @var  string */
    protected $route_action;
    /** @var  string */
    protected $route_class;
    /** @var  string */
    protected $route_method = '';
    /** @var  string */
    protected $url_action_one = '';
    /** @var bool */
    protected $use_cache;

    /**
     * Does the common stuff that is normally done in the __contruct method.
     * @param \Ritc\Library\Services\Di $o_di
     */
    protected function setupController(Di $o_di):void
    {
        $this->setObjects($o_di);
        $this->setProperties();
    }

    /**
     * Sets the class properties that are objects.
     * @param \Ritc\Library\Services\Di $o_di
     */
    protected function setObjects(Di $o_di):void
    {
        if (!$this->o_di instanceof Di) {
            $this->o_di = $o_di;
        }
        if (!$this->o_router instanceof Router) {
            $this->o_router = $o_di->get('router');
        }
        if (!$this->o_db instanceof DbModel) {
            $this->o_db = $o_di->get('db');
        }
        if (!$this->o_session instanceof Session) {
            $this->o_session = $o_di->get('session');
        }
        if (USE_CACHE) {
            $this->o_cache = new CacheHelper($this->o_di);
            $this->cache_type = $this->o_cache->getCacheType();
            $this->use_cache = empty($this->cache_type)
                ? false
                : true
            ;
        }
    }

    /**
     * Sets the class properties based on the route parts.
     */
    protected function setProperties():void
    {
        $a_router_parts       = $this->o_router->getRouteParts();
        $this->a_router_parts = $a_router_parts;
        $this->route_action   = $a_router_parts['route_action'];
        $this->a_post         = $a_router_parts['post'];
        $this->form_action    = $a_router_parts['form_action'];
        $this->route_class    = $a_router_parts['route_class'];
        $this->route_method   = $a_router_parts['route_method'];
        if (isset($a_router_parts['url_actions'][0])) {
            $this->a_url_actions  = $a_router_parts['url_actions'];
            $this->url_action_one = $a_router_parts['url_actions'][0];
        }
        if ($this->route_action !== '') {
            $this->main_action = $a_router_parts['route_action'];
        }
        elseif ($this->url_action_one !== '') {
            $this->main_action = $this->url_action_one;
        }
        elseif ($this->form_action !== '') {
            $this->main_action = $this->form_action;
        }
        else {
            $this->main_action = '';
        }
    }
}
