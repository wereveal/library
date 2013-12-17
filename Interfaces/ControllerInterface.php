<?php
namespace Ritc\Library\Interfaces;

interface ControllerInterface
{
    public function router(array $a_actions, array $a_values);

    ### Getters and Setters ###
    public function setDateFormat($value);
    public function setPhoneFormat($value);
    public function getDateFormat();
    public function getPhoneFormat();
}
