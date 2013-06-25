<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerCategoryObject
**/
class WerCategoryObject
{
    /**
     * @var integer $coId
    **/
    private $coId;

    /**
     * @var Wer\Sobi\Entity\WerObject
    **/
    private $coObject;

    /**
     * @var Wer\Sobi\Entity\WerCategory
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
     * @param Wer\Sobi\Entity\WerObject $coObject
    **/
    public function setCoObject(\Wer\Sobi\Entity\WerObject $coObject)
    {
        $this->coObject = $coObject;
    }

    /**
     * Get coObject
     * @return Wer\Sobi\Entity\WerObject
    **/
    public function getCoObject()
    {
        return $this->coObject;
    }

    /**
     * Set coCategory
     * @param Wer\Sobi\Entity\WerCategory $coCategory
    **/
    public function setCoCategory(\Wer\Sobi\Entity\WerCategory $coCategory)
    {
        $this->coCategory = $coCategory;
    }

    /**
     * Get coCategory
     * @return Wer\Sobi\Entity\WerCategory
    **/
    public function getCoCategory()
    {
        return $this->coCategory;
    }
}
