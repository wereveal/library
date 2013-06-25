<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerObjectData
**/
class WerObjectData
{
    /**
     * @var integer $odId
    **/
    private $odId;

    /**
     * @var text $odData
    **/
    private $odData;

    /**
     * @var datetime $odCreatedOn
    **/
    private $odCreatedOn;

    /**
     * @var datetime $odUpdatedOn
    **/
    private $odUpdatedOn;

    /**
     * @var Wer\Sobi\Entity\WerObject
    **/
    private $odObject;

    /**
     * @var Wer\Sobi\Entity\WerField
    **/
    private $odField;


    /**
     * Get odId
     * @return integer
    **/
    public function getOdId()
    {
        return $this->odId;
    }

    /**
     * Set odData
     * @param text $odData
    **/
    public function setOdData($odData)
    {
        $this->odData = $odData;
    }

    /**
     * Get odData
     * @return text
    **/
    public function getOdData()
    {
        return $this->odData;
    }

    /**
     * Set odCreatedOn
     * @param datetime $odCreatedOn
    **/
    public function setOdCreatedOn($odCreatedOn)
    {
        $this->odCreatedOn = $odCreatedOn;
    }

    /**
     * Get odCreatedOn
     * @return datetime
    **/
    public function getOdCreatedOn()
    {
        return $this->odCreatedOn;
    }

    /**
     * Set odUpdatedOn
     * @param datetime $odUpdatedOn
    **/
    public function setOdUpdatedOn($odUpdatedOn)
    {
        $this->odUpdatedOn = $odUpdatedOn;
    }

    /**
     * Get odUpdatedOn
     * @return datetime
    **/
    public function getOdUpdatedOn()
    {
        return $this->odUpdatedOn;
    }

    /**
     * Set odObject
     * @param Wer\Sobi\Entity\WerObject $odObject
    **/
    public function setOdObject(\Wer\Sobi\Entity\WerObject $odObject)
    {
        $this->odObject = $odObject;
    }

    /**
     * Get odObject
     * @return Wer\Sobi\Entity\WerObject
    **/
    public function getOdObject()
    {
        return $this->odObject;
    }

    /**
     * Set odField
     * @param Wer\Sobi\Entity\WerField $odField
    **/
    public function setOdField(\Wer\Sobi\Entity\WerField $odField)
    {
        $this->odField = $odField;
    }

    /**
     * Get odField
     * @return Wer\Sobi\Entity\WerField
    **/
    public function getOdField()
    {
        return $this->odField;
    }
}
