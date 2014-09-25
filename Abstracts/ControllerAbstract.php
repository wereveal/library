<?php
namespace Ritc\Library\Abstracts;

use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Core\Session;

abstract class ControllerAbstract implements ControllerInterface
{
    /**
     *  Router method mapping actions with class methods
     *  Needs to be overridden by the class extending this abstract
     *  @param array $a_actions
     *  @param array $a_values
     *  @return string $html normally html
    **/
    public function router(array $a_actions = array(), array $a_values = array())
    {
        return '';
    }

    ### GETTERS and SETTERS ###
    public function setSession(Session $o_session)
    {
        $this->o_session = $o_session;
    }
    public function getSession()
    {
        return $this->o_session;
    }
}
