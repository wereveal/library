<?php
/**
 *  @brief Common functions for the manager views.
 *  @file ManagerViewTraits.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Traits
 *  @class ManagerViewTraits
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-10-05 15:52:31
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - think it is working now - 10/05/2015 wer
 *      v0.1.0 - initial version         - 10/01/2015 wer
 *  </pre>
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\RoutesHelper;
use Ritc\Library\Services\Di;

trait ManagerViewTraits
{
    protected $a_links;
    protected $auth_level;
    protected $o_auth;
    protected $o_di;
    protected $o_router;
    protected $o_twig;

    /**
     *  The default setup for a view in the manager.
     *  @param Di $o_di
     */
    private function setupView(Di $o_di)
    {
        $this->setObjects($o_di);
        $this->setAuthLevel();
        $this->setLinks();
    }
    /**
     * Sets the standard used objects from the object injector.
     * @param Di $o_di
     */
    private function setObjects(Di $o_di)
    {
        $this->o_di     = $o_di;
        $this->o_auth   = new AuthHelper($o_di);
        $this->o_router = $o_di->get('router');
        $this->o_twig   = $o_di->get('twig');
    }
    /**
     *  Sets the class property $login_id to a value of the highest role level found or 999 if not found.
     *  @param string $login_id
     */
    private function setAuthLevel($login_id = '')
    {
        if ($login_id != '') {
            $this->auth_level = $this->o_auth->getHighestRoleLevel($login_id);
        }
        elseif (isset($_SESSION['login_id'])) {
            $this->auth_level = $this->o_auth->getHighestRoleLevel($_SESSION['login_id']);
        }
        else {
            $this->auth_level = 999;
        }
    }
    /**
     *  Sets an array of links used for the manager home page and for the menus.
     */
    private function setLinks()
    {
        $a_links = [
            [
                'text'        => 'Home',
                'url'         => '/manager/',
                'description' => 'Manager Home Page',
                'name'        => 'Home',
                'class'       => ''
            ],
            [
                'text'        => 'Constants Manger',
                'url'         => '/manager/constants/',
                'description' => 'Constant values changed and new constants added.',
                'name'        => 'Constants',
                'class'       => ''
            ],
            [
                'text'        => 'Routes Manager',
                'url'         => '/manager/routes/',
                'description' => 'Create and manage routes for the app.',
                'name'        => 'Routes',
                'class'       => ''
            ],
            [
                'text' => 'People Manager',
                'url'  => '/manager/people/',
                'description' => 'Create and manage people which get assigned to groups.',
                'name' => 'People',
                'class' => ''
            ],
            [
                'text'        => 'Groups Manager',
                'url'         => '/manager/groups/',
                'description' => 'Create and manage groups to which people are assigned.',
                'name'        => 'Groups',
                'class'       => ''
            ],
            [
                'text'        => 'Roles Manager',
                'url'         => '/manager/roles/',
                'description' => 'Create and manage roles to which groups are assigned.',
                'name'        => 'Roles',
                'class'       => ''
            ],
            [
                'text'        => 'Logout',
                'url'         => '/manager/logout/',
                'description' => 'Logout.',
                'name'        => 'Logout',
                'class'       => ''
            ]
        ];
        if ($this->auth_level == '') {
            $this->setAuthLevel();
        }
        $person_role_level = $this->auth_level;
        $current_route_path = $this->o_router->getRoutePath();
        $o_routes = new RoutesHelper($this->o_di, '');
        foreach ($a_links as $key => $a_link) {
            $o_routes->setRouteParts($a_link['url']);
            $a_route_parts = $o_routes->getRouteParts();
            $unset = $person_role_level <= $a_route_parts['min_role_level']
                ? false
                : true;
            if ($unset) {
                unset($a_links[$key]);
            }
            if ($a_link['url'] == $current_route_path) {
                $a_links[$key]['menu_class'] = 'active';
            }
        }
        $this->a_links = $a_links;
    }
}
