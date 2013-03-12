<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\GuideBundle\Entity\WerObject
**/
class WerObject
{
    /**
     * @var integer $obj_id
    **/
    private $obj_id;

    /**
     * @var text $obj_name
    **/
    private $obj_name;

    /**
     * @var datetime $obj_created_on
    **/
    private $obj_created_on;

    /**
     * @var datetime $obj_updated_on
    **/
    private $obj_updated_on;

    /**
     * @var boolean $obj_active
    **/
    private $obj_active;


    /**
     * Get obj_id
     * @return integer
    **/
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * Set obj_name
     * @param text $obj_name
    **/
    public function setObjName($obj_name = '')
    {
        $this->obj_name = $obj_name;
    }

    /**
     * Get obj_name
     * @return text
    **/
    public function getObjName()
    {
        return $this->obj_name;
    }

    /**
     * Set obj_created_on
     * @param datetime $obj_created_on
    **/
    public function setObjCreatedOn($obj_created_on = '')
    {
        $this->obj_created_on = $obj_created_on;
    }

    /**
     * Get obj_created_on
     * @return datetime
    **/
    public function getObjCreatedOn()
    {
        return $this->obj_created_on;
    }

    /**
     * Set obj_updated_on
     * @param datetime $obj_updated_on
    **/
    public function setObjUpdatedOn($obj_updated_on = '')
    {
        $this->obj_updated_on = $obj_updated_on;
    }

    /**
     * Get obj_updated_on
     * @return datetime
    **/
    public function getObjUpdatedOn()
    {
        return $this->obj_updated_on;
    }

    /**
     * Set obj_active
     * @param boolean $obj_active
    **/
    public function setObjActive($obj_active = '')
    {
        $this->obj_active = $obj_active;
    }

    /**
     * Get obj_active
     * @return boolean
    **/
    public function getObjActive()
    {
        return $this->obj_active;
    }
}