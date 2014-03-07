<?php
namespace Ritc\Library\Abstracts;

use Ritc\Library\Interfaces\ControllerInterface;
use Ritc\Library\Core\DatesTimes;

abstract class ControllerAbstract implements ControllerInterface
{
    protected $phone_format = "XXX-XXX-XXXX";
    protected $date_format  = "m/d/Y";

    /**
     *  Router method mapping actions with class methods
     *  Needs to be overridden by the class extending this abstract
     *  @param array $a_actions
     *  @param array $a_values
     *  @return string $html normally html
    **/
    public function routePage(array $a_actions = array(), array $a_values = array())
    {
        return '';
    }

    ### GETTERS and SETTERS ###
    /**
     *  Sets the value of $this->date_format.
     *  Verifies the date format is valid for php before
     *  setting it. If it isn't a valid format, doesn't set.
     *  @param string $value date format desired
     *  @return null
    **/
    public function setDateFormat($value = '')
    {
        if ($value == '') { return; }
        if (DatesTimes::isValidDateFormat($value)) {
            $this->date_format = $value;
        }
    }
    /**
     *  Sets the value of $phone_format
     *  Verifies value is valid format else does not set it.
     *  @param string $value defaults to ''
     *  @return null
    **/
    public function setPhoneFormat($value = '')
    {
        switch ($value) {
            case '(XXX) XXX-XXXX':
            case 'XXX XXX XXXX':
            case 'XXX.XXX.XXXX':
            case 'AAA-BBB-CCCC':
            case '(AAA) BBB-CCCC':
            case 'AAA BBB CCCC':
            case 'AAA.BBB.CCCC':
                $this->phone_format = $value;
                return;
            default:
                return;
        }
    }
    public function getDateFormat()
    {
        return $this->date_format;
    }
    public function getPhoneFormat()
    {
        return $this->phone_format;
    }

}
