<?php
/**
 *  @brief Common functions for the manager views.
 *  @file ManagerTraits.php
 *  @ingroup ritc_library Services
 *  @namespace Ritc/Library/Traits
 *  @class ManagerTraits
 *  @author William Reveal <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-10-01 14:11:44
 *  @note A part of the RITC Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - initial version - 10/01/2015 wer
 *  </pre>
 */
namespace Ritc\Library\Traits;

use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\RoutesHelper;

trait ManagerTraits
{
    protected $a_links;
    protected $o_auth;
    protected $o_di;
    protected $o_router;
    protected $o_twig;

    private function setObjects($o_di)
    {
        $this->o_di     = $o_di;
        $this->o_auth   = new AuthHelper($o_di);
        $this->o_router = $o_di->get('router');
        $this->o_twig   = $o_di->get('twig');
    }
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
        if (isset($_SESSION['login_id'])) {
            $person_role_level = $this->o_auth->getHighestRoleLevel($_SESSION['login_id']);
        }
        else {
            $person_role_level = 999;
        }
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