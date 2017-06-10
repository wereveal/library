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

use Ritc\Library\Services\Di;
use Ritc\Library\Tests\NavgroupsModelTester;
use Ritc\Library\Tests\NavigationModelTester;
use Ritc\Library\Tests\NavNgMapModelTester;
use Ritc\Library\Tests\PageModelTester;
use Ritc\Library\Tests\PeopleModelTester;
use Ritc\Library\Tests\UrlsModelTester;
use Ritc\Library\Traits\ControllerTraits;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Views\TestsView;

/**
 * Class TestsController
 * @class   TestsController
 * @package Ritc\Library\Controllers
 */
class TestsController
{
    use LogitTraits, ControllerTraits;

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
        $this->setupController($o_di);
        $this->o_view   = new TestsView($o_di);
        if (file_exists(LIBRARY_CONFIG_PATH . '/tests')) {
            $this->test_configs_path = LIBRARY_CONFIG_PATH . '/tests';
        }
    }

    /**
     * Main method for the controller.
     * Routes everything around from here.
     * @return string
     */
    public function route()
    {
        switch ($this->form_action) {
            case 'PeopleModel':
                $o_test = new PeopleModelTester($this->o_di);
                break;
            case 'PageModel':
                $o_test = new PageModelTester($this->o_di);
                break;
            case 'UrlsModel':
                $o_test = new UrlsModelTester($this->o_di);
                break;
            case 'NavgroupsModel':
                $o_test = new NavgroupsModelTester($this->o_di);
                break;
            case 'NavigationModel':
                $o_test = new NavigationModelTester($this->o_di);
                break;
            case 'NavNgMapModel':
                $o_test = new NavNgMapModelTester($this->o_di);
                break;
            case 'ConstantsModel':
            case 'GroupsModel':
            case 'Login':
            case 'RoutesModel':
            default:
                return $this->o_view->renderList();
        }
        $o_test->runTests();
        $a_test_results = $o_test->returnTestResults(true);
        return $this->o_view->renderResults($a_test_results);
    }
}
