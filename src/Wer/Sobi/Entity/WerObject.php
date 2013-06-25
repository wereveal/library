<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerObject
**/
class WerObject {
    /**
     * @var integer $objId
    **/
    private $objId;

    /**
     * @var text $objName
    **/
    private $objName;

    /**
     * @var datetime $objCreatedOn
    **/
    private $objCreatedOn;

    /**
     * @var datetime $objUpdatedOn
    **/
    private $objUpdatedOn;

    /**
     * @var boolean $objActive
    **/
    private $objActive;


    /**
     * Get objId
     * @return integer
    **/
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set objName
     * @param text $objName
    **/
    public function setObjName($objName)
    {
        $this->objName = $objName;
    }

    /**
     * Get objName
     * @return text
    **/
    public function getObjName()
    {
        return $this->objName;
    }

    /**
     * Set objCreatedOn
     * @param datetime $objCreatedOn
    **/
    public function setObjCreatedOn($objCreatedOn)
    {
        $this->objCreatedOn = $objCreatedOn;
    }

    /**
     * Get objCreatedOn
     * @return datetime
    **/
    public function getObjCreatedOn()
    {
        return $this->objCreatedOn;
    }

    /**
     * Set objUpdatedOn
     * @param datetime $objUpdatedOn
    **/
    public function setObjUpdatedOn($objUpdatedOn)
    {
        $this->objUpdatedOn = $objUpdatedOn;
    }

    /**
     * Get objUpdatedOn
     * @return datetime
    **/
    public function getObjUpdatedOn()
    {
        return $this->objUpdatedOn;
    }

    /**
     * Set objActive
     * @param boolean $objActive
    **/
    public function setObjActive($objActive)
    {
        $this->objActive = $objActive;
    }

    /**
     * Get objActive
     * @return boolean
    **/
    public function getObjActive()
    {
        return $this->objActive;
    }
}
