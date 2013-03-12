<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\GuideBundle\Entity\WerObjectData
**/
class WerObjectData
{
    /**
     * @var integer $od_id
    **/
    private $od_id;

    /**
     * @var text $od_data
    **/
    private $od_data;

    /**
     * @var datetime $od_created_on
    **/
    private $od_created_on;

    /**
     * @var datetime $od_updated_on
    **/
    private $od_updated_on;

    /**
     * @var int $od_object_id
    **/
    private $od_object_id;

    /**
     * @var int $od_field_id
    **/
    private $od_field_id;


    /**
     * Set od_id
     * @param int $od_id
    **/
    public function setOdId($od_id = '')
    {
        if ($od_id != '') {
            $this->od_id = $od_id;
        }
    }
    /**
     * Get od_id
     * @return integer
    **/
    public function getOdId()
    {
        return $this->od_id;
    }

    /**
     * Set od_data
     * @param text $od_data
    **/
    public function setOdData($od_data = '')
    {
        $this->od_data = $od_data;
    }

    /**
     * Get od_data
     * @return text
    **/
    public function getOdData()
    {
        return $this->od_data;
    }

    /**
     * Set od_created_on
     * @param datetime $od_created_on
    **/
    public function setOdCreatedOn($od_created_on = '')
    {
        $this->od_created_on = $od_created_on;
    }

    /**
     * Get od_created_on
     * @return datetime
    **/
    public function getOdCreatedOn()
    {
        return $this->od_created_on;
    }

    /**
     * Set od_updated_on
     * @param datetime $od_updated_on
    **/
    public function setOdUpdatedOn($od_updated_on = '')
    {
        $this->od_updated_on = $od_updated_on;
    }

    /**
     * Get od_updated_on
     * @return datetime
    **/
    public function getOdUpdatedOn()
    {
        return $this->od_updated_on;
    }

    /**
     * Set od_object_id
     * @param $od_object_id
    **/
    public function setOdObjectId($od_object_id = '')
    {
        $this->od_object_id = $od_object_id;
    }

    /**
     * Get od_object_id
     * @return int
    **/
    public function getOdObjectID()
    {
        return $this->od_object_id;
    }

    /**
     * Set od_field_id
     * @param $od_field_id
    **/
    public function setOdFieldId($od_field_id = '')
    {
        $this->od_field_id = $od_field_id;
    }

    /**
     * Get od_field
     * @return int
    **/
    public function getOdFieldId()
    {
        return $this->od_field_id;
    }
}