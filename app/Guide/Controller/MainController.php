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
    protected $a_get;
    protected $a_post;
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
        switch ($this->action1) {
            case 'search':
                $o_search = new namespace\SearchController();
                return $o_search->router($this->action2, $this->action3, $this->a_get);
            case 'list':
                $o_search = new namespace\SearchController();
                return $o_search->listAction($this->action2, $this->action3);
            case 'item':
                $o_item = new namespace\ItemController();
                return $o_item->defaultAction($this->action2);
            case 'home':
            default:
                $o_home = new namespace\HomeController();
                return $o_home->defaultAction();
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
        $a_actions          = $this->o_actions->getUriActions();
        $this->action1     = isset($a_actions['action1']) ? $a_actions['action1'] : '' ;
        $this->action2     = isset($a_actions['action2']) ? $a_actions['action2'] : '' ;
        $this->action3     = isset($a_actions['action3']) ? $a_actions['action3'] : '' ;
        $this->form_action = $this->o_actions->getFormAction();
        $this->a_post      = $this->o_actions->getCleanPost();
        $this->a_get       = $this->o_actions->getCleanGet();
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
    public function getFormAction()
    {
        return $this->form_action;
    }
    public function getPost()
    {
        return $this->a_post;
    }
    public function setAction1($value = '')
    {
        return; // not gonna do it
    }
    public function setAction2($value = '')
    {
        return; // not gonna do it
    }
    public function setAction3($value = '')
    {
        return; // not gonna do it
    }
    public function setFormAction($value = '')
    {
        return; // not gonna do it
    }
    public function setPost($value = '')
    {
        return; // not gonna do it
    }

}
