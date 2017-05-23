<?php
/**
 * @brief     Controller for the Test page.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/TestsController.phpnamespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.1
 * @date      2017-02-15 15:23:02
 * @note <b>Change Log</b>
 * - v1.0.0-alpha.1 - Refactoring                                   - 2017-02-15 wer
 * - v1.0.0-alpha.0 - Initial version                               - 10/23/2015 wer
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Router;
use Ritc\Library\Tests\PageModelTester;
use Ritc\Library\Tests\PeopleModelTester;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\TestsView;

/**
 * Class TestsController
 * @class   TestsController
 * @package Ritc\Library\Controllers
 */
class TestsController
{
    use LogitTraits;

    /** @var DbModel */
    private $o_db;
    /** @var Di */
    private $o_di;
    /** @var Router */
    private $o_router;
    /** @var TestsView */
    private $o_view;
    /** @var string */
    private $test_configs_path;

    /**
     * TestsController constructor.
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupElog($o_di);
        $this->o_di     = $o_di;
        $this->o_db     = $o_di->get('db');
        $this->o_router = $o_di->get('router');
        $this->o_view   = new TestsView($o_di);
        if (file_exists(LIBRARY_CONFIG_PATH . '/tests')) {
            $this->test_configs_path = LIBRARY_CONFIG_PATH . '/tests';
        }
        elseif (file_exists(SRC_CONFIG_PATH . '/tests')) {
            $this->test_configs_path = SRC_CONFIG_PATH . '/tests';
        }
        else {
            $this->test_configs_path = __DIR__;
        }
    }
    /**
     * Main method for the controller.
     * Routes everything around from here.
     * @return string
     */
    public function route()
    {
        $a_route_parts = $this->o_router->getRouteParts();
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
