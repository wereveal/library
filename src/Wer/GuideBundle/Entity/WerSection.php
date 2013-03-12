<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
    Wer\GuideBundle\Entity\WerSection
**/
class WerSection
{
    /**
     * @var integer $sec_id
    **/
    private $sec_id;

    /**
     * @var text $sec_name
    **/
    private $sec_name;

    /**
     * @var text $sec_description
    **/
    private $sec_description;

    /**
     * @var boolean $sec_active
    **/
    private $sec_active;


    /**
     * Get sec_id
     * @return integer
    **/
    public function getSecId()
    {
        return $this->sec_id;
    }

    /**
     * Set sec_name
     * @param text $sec_name
    **/
    public function setSecName($sec_name = '')
    {
        $this->sec_name = $sec_name;
    }

    /**
     * Get sec_name
     * @return text
    **/
    public function getSecName()
    {
        return $this->sec_name;
    }

    /**
     * Set sec_description
     * @param text $sec_description
    **/
    public function setSecDescription($sec_description = '')
    {
        $this->sec_description = $sec_description;
    }

    /**
     * Get sec_description
     * @return text
    **/
    public function getSecDescription()
    {
        return $this->sec_description;
    }

    /**
     * Set sec_active
     * @param boolean $sec_active
    **/
    public function setSecActive($sec_active = '')
    {
        $this->sec_active = $sec_active;
    }

    /**
     * Get sec_active
     * @return boolean
    **/
    public function getSecActive()
    {
        return $this->sec_active;
    }
}