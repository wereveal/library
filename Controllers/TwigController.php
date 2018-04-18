<?php
/**
 * @brief     Admin for Twig config.
 * @ingroup   lib_controllers
 * @file      Ritc/Library/Controllers/TwigController.php
 * @namespace Ritc\Library\Controllers
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-14 14:36:29
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-14 wer
 * @todo Ritc/Library/Controllers/TwigController.php - Everything
 */
namespace Ritc\Library\Controllers;

use Ritc\Library\Helper\ViewHelper;
use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\ConfigControllerTraits;
use Ritc\Library\Views\TwigView;

/**
 * Class TwigController.
 * @class   TwigController
 * @package Ritc\Library\Controllers
 */
class TwigController implements ControllerInterface
{
    use ConfigControllerTraits;

    /** @var \Ritc\Library\Views\TwigView  */
    private $o_view;

    /**
     * TwigController constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->setupManagerController($o_di);
        $this->o_view = new TwigView($this->o_di);
    }

    /**
     * Routes things around to do the Twig management.
     * @return string
     */
    public function route()
    {
        $a_message = [];
        $action = empty($this->form_action)
            ? 'renderList'
            : $this->form_action;
        if ($this->o_session->isNotValidSession($this->a_post, true)) {
            $this->o_auth->logout($_SESSION['login_id']);
            $o_main = $this->o_di->get('mainController');
            if (is_object($o_main)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $o_main->route();
            }
        }
        switch ($action) {
            case 'new_tpl':
                break;
            case 'new_tp':
                break;
            case 'new_dir':
                break;
            case 'update_tpl':
                break;
            case 'update_tp':
                break;
            case 'update_dir':
                break;
            case 'verify_delete_tpl':
                return $this->verifyDelete('tpl');
            case 'verify_delete_tp':
                return $this->verifyDelete('tp');
            case 'verify_delete_dir':
                return $this->verifyDelete('dir');
            case 'delete_tpl':
                break;
            case 'delete_tp':
                break;
            case 'delete_dir':
                break;
            case 'renderList':
            default:
                // just render the list
        }
        return $this->o_view->render($a_message);
    }

    /**
     * Routes to the correct verifyDelete form.
     * @param string $which_one
     * @return array|string
     */
    private function verifyDelete($which_one = '')
    {
        if (empty($which_one)) {
            $a_message = ViewHelper::errorMessage('Could not perform the delete. Missing a required value.');
            return $this->o_view->render($a_message);
        }
        switch ($which_one) {
            case 'tpl':
                if (empty($this->a_post['tpl_id'])) {
                    return $this->o_view->render(ViewHelper::errorMessage('Could not delete the template.'));
                }
                return $this->o_view->renderDeleteTpl($this->a_post['tpl_id']);
            case 'tp':
                if (empty($this->a_post['tp_id'])) {
                    return $this->o_view->render(ViewHelper::errorMessage('Could not delete the twig prefix.'));
                }
                return $this->o_view->renderDeleteTp($this->a_post['tp_id']);
            case 'dir':
                if (empty($this->a_post['td_id'])) {
                    return $this->o_view->render(ViewHelper::errorMessage('Could not delete the twig prefix.'));
                }
                return $this->o_view->renderDeleteDir($this->a_post['td_id']);
            default:
                return ViewHelper::errorMessage('Missing a valid value.');
        }
    }
}
