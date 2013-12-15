<?php
/**
 *  Abstract class that provides common methods used for page controllers in the app.
 *  @file PageControllerAbstract.php
 *  @namespace Ritc\Library\Abstracts
 *  @class PageControllerAbstract
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2013-12-12 12:36:21
 *  @par Change Log
 *      v1.0.0 - Initial version 2013-12-12
 *  @par Ritc Library version 4.0
 *  @ingroup library controller
**/
namespace Ritc\Library\Abstracts;

abstract class PageControllerAbstract implements namespace\PageControllerInterface
{
    /**
     *  Main Router and Puker outer (more descriptive method name).
     *  Turns over the hard work to the specific controllers through the router.
     *  @param none
     *  @return str $html
    **/
    public function renderPage()
    {
        $this->o_actions->setUriActions();
        $a_actions   = $this->o_actions->getUriActions();
        $form_action = $this->o_actions->getFormAction();
        $a_post      = $this->o_actions->getCleanPost();
        $a_get       = $this->o_actions->getCleanGet();
        $a_values    = array('form_action'=>$form_action);
        $a_values    = array_merge($a_values, $a_get, $a_post);
        return $this->router($a_actions, $a_values);
    }
    /**
     *  Routes the code to the appropriate sub controllers and returns a string.
     *  @param array $a_actions optional, the actions derived from the URL/Form
     *  @param array $a_values optional, the values from a form
     *  @return str normally html to be displayed.
    **/
    public function router(array $a_actions = array(), array $a_values = array())
    {
        return '';
    }
}
