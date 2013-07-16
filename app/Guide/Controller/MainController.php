<?php
/**
 *  The main Controller for the whole site.
 *  @file MainController.php
 *  @class MainController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1
 *  @par Change Log
 *      v0.1 - Initial version 07/02/2013 16:07:23
 *  @par Wer Guide version 1.0
 *  @date 2013-07-02 16:07:17
 *  @ingroup guide controller
**/
namespace Wer\Guide\Controller;

use Wer\Framework\Library\Elog;
use Wer\Framework\Library\Session;
use Wer\Framework\Library\Actions;

class MainController
{
    protected $action1;
    protected $action2;
    protected $action3;
    protected $form_action;
    protected $a_actions;
    protected $a_get;
    protected $a_post;
    protected $a_values;
    protected $o_actions;
    protected $o_elog;
    protected $o_sess;
    public function __construct()
    {
        $this->o_elog    = Elog::start();
        $this->o_sess    = Session::start();
        $this->o_actions = new Actions;
        $this->initializeValues();
    }
    /**
     *  Main Router and Puker outer.
     *  Turns over the hard work to the specific controllers.
     *  @param none
     *  @return str $html
    **/
    public function renderPage()
    {
        if (isset($this->a_actions['action1'])) {
            switch ($this->a_actions['action1']) {
                case 'list':
                case 'search':
                    $o_search = new namespace\SearchController();
                    return $o_search->router($this->a_actions, $this->a_values);
                case 'item':
                    $o_item = new namespace\ItemController();
                    return $o_item->router($this->a_actions);
                case 'home':
                default:
                    $o_home = new namespace\HomeController();
                    return $o_home->router($this->a_actions);
            }
        } else {
            $o_home = new namespace\HomeController();
            return $o_home->router();
        }
    }
    /**
     *  Initializes the values used to specify what actions are happening
     *  @param none
     *  @return null
    **/
    protected function initializeValues()
    {
        $this->o_actions->setUriActions();
        $this->a_actions   = $this->o_actions->getUriActions();
        $this->form_action = $this->o_actions->getFormAction();
        $this->a_post      = $this->o_actions->getCleanPost();
        $this->a_get       = $this->o_actions->getCleanGet();
        $a_values          = array('form_action'=>$this->form_action);
        $this->a_values    = array_merge($a_values, $this->a_get, $this->a_post);
    }

    ### GETTERS and SETTERS ###
    public function getAction1()
    {
        return $this->action1;
    }
    public function getAction2()
    {
        return $this->action2;
    }
    public function getAction3()
    {
        return $this->action3;
    }
    public function getActions()
    {
        return $this->a_actions;
    }
    public function getFormAction()
    {
        return $this->form_action;
    }
    public function getGet()
    {
        return $this->a_get;
    }
    public function getPost()
    {
        return $this->a_post;
    }
    public function setAction1()
    {
        return; // not gonna allow it
    }
    public function setAction2()
    {
        return; // not gonna allow it
    }
    public function setAction3()
    {
        return; // not gonna allow it
    }
    public function setActions()
    {
        return; // not gonna allow it
    }
    public function setFormAction()
    {
        return; // not gonna allow it
    }
    public function setGet()
    {
        return; // not gonna allow it
    }
    public function setPost()
    {
        return; // not gonna allow it
    }

}
