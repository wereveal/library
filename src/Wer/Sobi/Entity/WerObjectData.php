<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\SobiBundle\Entity\WerObjectData
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
     * @var Wer\SobiBundle\Entity\WerObject
    **/
    private $odObject;

    /**
     * @var Wer\SobiBundle\Entity\WerField
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
     * @param Wer\SobiBundle\Entity\WerObject $odObject
    **/
    public function setOdObject(\Wer\SobiBundle\Entity\WerObject $odObject)
    {
        $this->odObject = $odObject;
    }

    /**
     * Get odObject
     * @return Wer\SobiBundle\Entity\WerObject
    **/
    public function getOdObject()
    {
        return $this->odObject;
    }

    /**
     * Set odField
     * @param Wer\SobiBundle\Entity\WerField $odField
    **/
    public function setOdField(\Wer\SobiBundle\Entity\WerField $odField)
    {
        $this->odField = $odField;
    }

    /**
     * Get odField
     * @return Wer\SobiBundle\Entity\WerField
    **/
    public function getOdField()
    {
        return $this->odField;
    }
}
