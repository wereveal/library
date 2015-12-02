<?php
/**
 *  @brief     Controller for the Test page.
 *  @ingroup   ritc_library controllers
 *  @file      TestsAdminController.php
 *  @namespace Ritc\Library\Controllers
 *  @class     TestsAdminController
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.0.0β1
 *  @date      2015-10-23 11:43:13
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version                              - 10/23/2015 wer
 *  </pre>
**/
namespace Ritc\Library\Controllers;

use Ritc\Library\Services\Di;
use Ritc\Library\Tests\PageModelTester;
use Ritc\Library\Tests\PeopleModelTester;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\TestsAdminView;

class TestsAdminController
{
    use LogitTraits;

    private $o_db;
    private $o_di;
    private $o_router;
    private $o_view;
    private $test_configs_path;

    public function __construct(Di $o_di)
    {
        $this->o_di     = $o_di;
        $this->o_db     = $o_di->get('db');
        $this->o_router = $o_di->get('router');
        $this->o_view   = new TestsAdminView($o_di);
        if (file_exists(LIBRARY_CONFIG_PATH . '/tests')) {
            $this->test_configs_path = LIBRARY_CONFIG_PATH . '/tests';
        }
        elseif (file_exists(APP_CONFIG_PATH . '/tests')) {
            $this->test_configs_path = APP_CONFIG_PATH . '/tests';
        }
        else {
            $this->test_configs_path = __DIR__;
        }
        if (DEVELOPER_MODE) {
            $this->o_elog = $o_di->get('elog');
            $this->o_view->setElog($this->o_elog);
        }
    }
    /**
     *  Main method for the controller.
     *  Routes everything around from here.
     *  @return string
     */
    public function render()
    {
        $a_route_parts = $this->o_router->getRouterParts();
        $main_action   = $a_route_parts['route_action'];
        $url_actions   = $a_route_parts['url_actions'];
        $url_action    = isset($url_actions[0])
            ? $a_route_parts['url_actions'][0]
            : '';
        if ($main_action == '' && $url_action != '') {
            $main_action = $url_action;
        }
        switch ($main_action) {
            case 'PeopleModel':
                $o_test = new PeopleModelTester($this->o_di);
                break;
            case 'PageModel':
                $o_test = new PageModelTester($this->o_di);
                break;
            case 'ConstantsModel':
            case 'GroupsModel':
            case 'Login':
            case 'RoutesModel':
            default:
                return $this->o_view->renderList();
        }
        $a_order  = include $this->test_configs_path . '/' . $main_action . '_test_order.php';
        $a_values = include $this->test_configs_path . '/' . $main_action . '_test_values.php';
        $o_test->setTestOrder($a_order);
        $o_test->setTestValues($a_values);
        $a_test_results = $o_test->runTests();
        return $this->o_view->renderResults($a_test_results);
    }
}
