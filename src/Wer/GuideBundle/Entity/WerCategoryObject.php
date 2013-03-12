<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
    Wer\GuideBundle\Entity\WerCategoryObject
**/
class WerCategoryObject {
    /**
     * @var integer $co_id
    **/
    private $co_id;

    /**
     * @var Wer\GuideBundle\Entity\WerObject
    **/
    private $co_object;

    /**
     * @var Wer\GuideBundle\Entity\WerCategory
    **/
    private $co_category;


    /**
     * Get co_id
     * @return integer
    **/
    public function getCoId()
    {
        return $this->co_id;
    }

    /**
     * Set co_object
     * @param Wer\GuideBundle\Entity\WerObject $co_object
    **/
    public function setCoObject(\Wer\GuideBundle\Entity\WerObject $co_object)
    {
        $this->co_object = $co_object;
    }

    /**
     * Get co_object
     * @return Wer\GuideBundle\Entity\WerObject
    **/
    public function getCoObject()
    {
        return $this->co_object;
    }

    /**
     * Set co_category
     * @param Wer\GuideBundle\Entity\WerCategory $co_category
    **/
    public function setCoCategory(\Wer\GuideBundle\Entity\WerCategory $co_category)
    {
        $this->co_category = $co_category;
    }

    /**
     * Get co_category
     * @return Wer\GuideBundle\Entity\WerCategory
    **/
    public function getCoCategory()
    {
        return $this->co_category;
    }
}