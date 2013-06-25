<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerSection
**/
class WerSection
{
    /**
     * @var integer $secId
    **/
    private $secId;

    /**
     * @var text $secName
    **/
    private $secName;

    /**
     * @var text $secDescription
    **/
    private $secDescription;

    /**
     * @var boolean $secActive
    **/
    private $secActive;


    /**
     * Get secId
     * @return integer
    **/
    public function getSecId()
    {
        return $this->secId;
    }

    /**
     * Set secName
     * @param text $secName
    **/
    public function setSecName($secName)
    {
        $this->secName = $secName;
    }

    /**
     * Get secName
     * @return text
    **/
    public function getSecName()
    {
        return $this->secName;
    }

    /**
     * Set secDescription
     * @param text $secDescription
    **/
    public function setSecDescription($secDescription)
    {
        $this->secDescription = $secDescription;
    }

    /**
     * Get secDescription
     * @return text
    **/
    public function getSecDescription()
    {
        return $this->secDescription;
    }

    /**
     * Set secActive
     * @param boolean $secActive
    **/
    public function setSecActive($secActive)
    {
        $this->secActive = $secActive;
    }

    /**
     * Get secActive
     * @return boolean
    **/
    public function getSecActive()
    {
        return $this->secActive;
    }
}
