<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\SobiBundle\Entity\WerCategoryObject
**/
class WerCategoryObject
{
    /**
     * @var integer $coId
    **/
    private $coId;

    /**
     * @var Wer\SobiBundle\Entity\WerObject
    **/
    private $coObject;

    /**
     * @var Wer\SobiBundle\Entity\WerCategory
    **/
    private $coCategory;


    /**
     * Get coId
     * @return integer
    **/
    public function getCoId()
    {
        return $this->coId;
    }

    /**
     * Set coObject
     * @param Wer\SobiBundle\Entity\WerObject $coObject
    **/
    public function setCoObject(\Wer\SobiBundle\Entity\WerObject $coObject)
    {
        $this->coObject = $coObject;
    }

    /**
     * Get coObject
     * @return Wer\SobiBundle\Entity\WerObject
    **/
    public function getCoObject()
    {
        return $this->coObject;
    }

    /**
     * Set coCategory
     * @param Wer\SobiBundle\Entity\WerCategory $coCategory
    **/
    public function setCoCategory(\Wer\SobiBundle\Entity\WerCategory $coCategory)
    {
        $this->coCategory = $coCategory;
    }

    /**
     * Get coCategory
     * @return Wer\SobiBundle\Entity\WerCategory
    **/
    public function getCoCategory()
    {
        return $this->coCategory;
    }
}
