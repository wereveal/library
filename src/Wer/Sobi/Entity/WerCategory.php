<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerCategory
**/
class WerCategory {
    /**
     * @var integer $catId
    **/
    private $catId;

    /**
     * @var text $catName
    **/
    private $catName;

    /**
     * @var text $catDescription
    **/
    private $catDescription;

    /**
     * @var text $catIcon
    **/
    private $catIcon;

    /**
     * @var boolean $catActive
    **/
    private $catActive;

    /**
     * @var Wer\Sobi\Entity\WerCategory
    **/
    private $crParent;

    public function __construct()
    {
        $this->crParent = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set the Cat Id for the import
     * @param int $cat_id
    **/
    public function setCatId( $cat_id )
    {
        $this->catId = $cat_id;
    }
    /**
     * Get catId
     * @return integer
    **/
    public function getCatId()
    {
        return $this->catId;
    }

    /**
     * Set catName
     * @param text $catName
    **/
    public function setCatName($catName)
    {
        $this->catName = $catName;
    }

    /**
     * Get catName
     * @return text
    **/
    public function getCatName()
    {
        return $this->catName;
    }

    /**
     * Set catDescription
     * @param text $catDescription
    **/
    public function setCatDescription($catDescription)
    {
        $this->catDescription = $catDescription;
    }

    /**
     * Get catDescription
     * @return text
    **/
    public function getCatDescription()
    {
        return $this->catDescription;
    }

    /**
     * Set catIcon
     * @param text $catIcon
    **/
    public function setCatIcon($catIcon)
    {
        $this->catIcon = $catIcon;
    }

    /**
     * Get catIcon
     * @return text
    **/
    public function getCatIcon()
    {
        return $this->catIcon;
    }

    /**
     * Set catActive
     * @param boolean $catActive
    **/
    public function setCatActive($catActive)
    {
        $this->catActive = $catActive;
    }

    /**
     * Get catActive
     * @return boolean
    **/
    public function getCatActive()
    {
        return $this->catActive;
    }

    /**
     * Add crParent
     * @param Wer\Sobi\Entity\WerCategory $crParent
    **/
    public function addWerCategory(\Wer\Sobi\Entity\WerCategory $crParent)
    {
        $this->crParent[] = $crParent;
    }

    /**
     * Get crParent
     * @return Doctrine\Common\Collections\Collection
    **/
    public function getCrParent()
    {
        return $this->crParent;
    }
}
