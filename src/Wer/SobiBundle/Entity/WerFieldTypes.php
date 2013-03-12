<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\SobiBundle\Entity\WerFieldTypes
**/
class WerFieldTypes
{
    /**
     * @var integer $ftId
    **/
    private $ftId;

    /**
     * @var text $ftName
    **/
    private $ftName;

    /**
     * @var text $ftType
    **/
    private $ftType;


    /**
     * Get ftId
     * @return integer
    **/
    public function getFtId()
    {
        return $this->ftId;
    }

    /**
     * Set ftName
     * @param text $ftName
    **/
    public function setFtName($ftName)
    {
        $this->ftName = $ftName;
    }

    /**
     * Get ftName
     * @return text
    **/
    public function getFtName()
    {
        return $this->ftName;
    }

    /**
     * Set ftType
     * @param text $ftType
    **/
    public function setFtType($ftType)
    {
        $this->ftType = $ftType;
    }

    /**
     * Get ftType
     * @return text
    **/
    public function getFtType()
    {
        return $this->ftType;
    }
}
