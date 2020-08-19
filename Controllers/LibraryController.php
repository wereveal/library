<?php
/**
 * Class LibraryController
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Controllers;

use ReflectionException;
use Ritc\Library\Exceptions\ControllerException;
use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\LibraryView;

/**
 * Main controller for the config manager.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.1
 * @date    2017-05-14 14:35:02
 * @change_log
 * - v2.1.1   - bug fix                               - 2017-05-14 wer
 * - v2.1.0   - Added UrlAdminController              - 2016-04-11 wer
 * - v2.0.0   - Renamed Class to be more specific     - 2016-03-31 wer
 * - v1.0.1   - needed to change private to protected - 12/01/2015 wer
 *              in order to extend this class.
 * - v1.0.0   - first working version                 - 11/27/2015 wer
 * - v1.0.0β7 - added page controller                 - 11/12/2015 wer
 * - v1.0.0β6 - added tests controller                - 10/23/2015 wer
 * - v1.0.0β5 - working for groups                    - 09/25/2015 wer
 * - v1.0.0β4 - working for Roles                     - 01/28/2015 wer
 * - v1.0.0β3 - working for router and config         - 01/16/2015 wer
 * - v1.0.0β2 - Set up to render a basic landing page - 01/06/2015 wer
 * - v1.0.0β1 - changed to use IOC                    - 11/17/2014 wer
 * - v1.0.0α1 - Initial version                       - 11/14/2014 wer
 */
class LibraryController implements ControllerInterface
{
    use LogitTraits;
    use ConfigControllerTraits;

    /** @var LibraryView */
    protected $o_view;

    /**
     * LibraryController constructor.
     *
     * @param Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view = new LibraryView($this->o_di);
        $this->setupElog($o_di);
        if (!defined('LIB_TWIG_PREFIX')) {
            /** @var string LIB_TWIG_PREFIX */
            define('LIB_TWIG_PREFIX', 'lib_');
        }
    }

    /**
     * Default page for the library manager and login.
     *
     * @return string
     */
    public function route():string
    {
        if ($this->loginValid()) {
            $o_c = '';
            switch ($this->route_action) {
                case 'login':
                    return $this->o_view->renderLogin();
                    break;
                case 'logout':
                    $this->o_auth->logout($_SESSION['login_id']);
                    header('Location: ' . SITE_URL . '/manager/');
                    break;
                case 'ajax':
                    $o_c = new AjaxController($this->o_di);
                    break;
                case 'alias':
                    $a_message = ViewHelper::infoMessage('Not Available Yet');
                    return $this->o_view->renderError($a_message);
                case 'blocks':
                    $o_c = new BlocksController($this->o_di);
                    break;
                case 'cache':
                    $o_c = new CacheManagerController($this->o_di);
                    break;
                case 'constants':
                    $o_c = new ConstantsController($this->o_di);
                    break;
                case 'content':
                    $o_c = new ContentController($this->o_di);
                    break;
                case 'groups':
                    $o_c = new GroupsController($this->o_di);
                    break;
                case 'navigation':
                    $o_c = new NavigationController($this->o_di);
                    break;
                case 'navgroups':
                    $o_c = new NavgroupsController($this->o_di);
                    break;
                case 'pages':
                    try {
                        $o_c = new PageController($this->o_di);
                    }
                    catch (ControllerException $e) {
                        $a_message = ViewHelper::errorMessage($e->getMessage());
                        return $this->o_view->renderLandingPage($a_message);
                    }
                    break;
                case 'people':
                    $o_c = new PeopleController($this->o_di);
                    break;
                case 'routes':
                    $o_c = new RoutesController($this->o_di);
                    break;
                case 'sitemap':
                    $o_c = new LibSitemapController($this->o_di);
                    break;
                case 'tests':
                    $o_c = new TestsController($this->o_di);
                    break;
                case 'twig':
                    $o_c = new TwigController($this->o_di);
                    break;
                case 'urls':
                    $o_c = new UrlsController($this->o_di);
                    break;
                case '':
                default:
                    return $this->o_view->renderLandingPage();
            }
            if (is_object($o_c)) {
                try {
                    return $o_c->route();
                }
                catch (ReflectionException $e) {
                    $a_message = ViewHelper::errorMessage('Could not find the page requested.');
                    return $this->o_view->renderError($a_message);
                }
            }
            else {
                $a_message = ViewHelper::errorMessage('Could not find the page requested.');
                return $this->o_view->renderError($a_message);
            }
        }
        elseif ($this->form_action === 'verifyLogin' || $this->route_action === 'verifyLogin') {
            $a_message = $this->verifyLogin();
            if ($a_message['type'] === 'success') {
                return $this->o_view->renderLandingPage($a_message);
            }

            $login_id = $this->a_post['login_id'] ?? '';
            $a_values = [
                'tpl'       => 'login',
                'location'  => '/manager/config/',
                'login_id'  => $login_id,
                'a_message' => $a_message
            ];
            return $this->o_view->renderLogin($a_values);
        }
        else {
            return $this->o_view->renderLogin();
        }
    }
}
