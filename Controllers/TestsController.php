<?php /** @noinspection PhpUndefinedConstantInspection */

/**
 * Class TestsController
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use ReflectionException;
use Ritc\Library\Services\Di;
use Ritc\Library\Tests\ConstantsModelTester;
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
 * Class TestsController - Controller for the Test page.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.2
 * @date    2021-11-26 15:22:23
 * @change_log
 * - v1.0.0-alpha.2 - php8 compatibility                        - 2021-11-26 wer
 * - v1.0.0-alpha.1 - Refactoring                               - 2017-02-15 wer
 * - v1.0.0-alpha.0 - Initial version                           - 10/23/2015 wer
 */
class TestsController
{
    use LogitTraits;
    use ControllerTraits;

    /** @var TestsView view object */
    private TestsView $o_view;
    /** @var string path to the config file */
    private string $test_configs_path;

    /**
     * TestsController constructor.
     *
     * @param Di $o_di
     * @noinspection UnusedConstructorDependenciesInspection
     */
    public function __construct(Di $o_di)
    {
        $this->setupController($o_di);
        $this->o_view   = new TestsView($o_di);
        $this->a_object_names = [];
        $this->setupElog($o_di);
        if (file_exists(LIBRARY_CONFIG_PATH . '/tests')) {
            $this->test_configs_path = LIBRARY_CONFIG_PATH . '/tests';
        }
    }

    /**
     * Main method for the controller.
     * Routes everything around from here.
     *
     * @return string
     * @throws ReflectionException
     */
    public function route():string
    {
        switch ($this->form_action) {
            case 'ConstantsModel':
                $o_test = new ConstantsModelTester($this->o_di);
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
            case 'PageModel':
                $o_test = new PageModelTester($this->o_di);
                break;
            case 'PeopleModel':
                $o_test = new PeopleModelTester($this->o_di);
                break;
            case 'GroupsModel':
            case 'Login':
            case 'RoutesModel':
            default:
                return $this->o_view->renderList();
        }
        $a_test_results = $o_test->runTests();
        $o_test->cleanupDbTests();
        return $this->o_view->renderResults($a_test_results);
    }
}
