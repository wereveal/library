<?php
/**
 *  The main Controller for the whole site.
 *  @file IndexController.php
 *  @class IndexController
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1
 *  @par Change Log
 *      v0.1 - Initial version 07/02/2013 16:07:23
 *  @par Wer Guide version 1.0
 *  @date 2013-07-02 16:07:17
 *  @ingroup guide controller
**/
namespace Wer\Guide\Controller;

class IndexController
{
    protected $action1;
    protected $action2;
    protected $action3;
    protected $form_action;
    protected $a_post;
    protected $o_actions;
    protected $o_elog;
    protected $o_sess;
    public function __construct()
    {
        $this->o_elog     = Elog::start();
        $this->o_sess     = Session::start();
        $this->o_actions  = new Actions;
        $this->initializeValues();
    }
    /**
     *  Main Router and Puker outer.
     *  @param none
     *  @return str $html
    **/
    public function renderPage()
    {
        switch ($this->action1) {
            case 'search':
                return $this->o_search_controller->indexAction($this->action2, $this->action3, $this->a_post);
            case 'list':
                return $this->o_search_controller->listAction($this->action2, $this->action3);
            case 'item':
                return $this->o_item_controller->indexAction($this->action2);
            default:
                return $this->o_main_controller->indexAction();
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
        $a_actions          = $this->o_actions->getUriActoins();
        $this->action1     = isset($a_actions['action1']) ? $a_actions['action1'] : '' ;
        $this->action2     = isset($a_actions['action2']) ? $a_actions['action2'] : '' ;
        $this->action3     = isset($a_actions['action3']) ? $a_actions['action3'] : '' ;
        $this->form_action = $this->o_actions->getFormAction();
        $this->a_post      = $this->o_array->cleanArrayValues($_POST);
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
