<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Wer\GuideBundle\Entity\WerCategory
**/
class WerCategory {
    /**
     * @var integer $cat_id
    **/
    private $cat_id;

    /**
     * @var text $cat_name
    **/
    private $cat_name;

    /**
     * @var text $cat_description
    **/
    private $cat_description;

    /**
     * @var text $cat_icon
    **/
    private $cat_icon;

    /**
     * @var boolean $cat_active
    **/
    private $cat_active;

    /**
     * @var $a_my_parents
    **/
    private $a_my_parents;

    /**
     * @var $a_my_children
    **/
    private $a_my_children;

    public function __construct()
    {
        $this->a_my_parents = new ArrayCollection();
        $this->a_my_children = new ArrayCollection();
    }
    /**
     * Get cat_id
     * @return integer
    **/
    public function getCatId()
    {
        return $this->cat_id;
    }
    /**
     * Set cat_name
     * @param text $cat_name required
    **/
    public function setCatName($cat_name = '')
    {
        if ($cat_name != '') {
            $this->cat_name = $cat_name;
        }
    }

    /**
     * Get cat_name
     * @return text
    **/
    public function getCatName()
    {
        return $this->cat_name;
    }

    /**
     * Set cat_description
     * @param text $cat_description optional
    **/
    public function setCatDescription($cat_description = '')
    {
        $this->cat_description = $cat_description;
    }

    /**
     * Get cat_description
     * @return text
    **/
    public function getCatDescription()
    {
        return $this->cat_description;
    }

    /**
     * Set cat_icon
     * @param text $cat_icon optional
    **/
    public function setCatIcon($cat_icon = '')
    {
            $this->cat_icon = $cat_icon;
    }

    /**
     * Get cat_icon
     * @return text
    **/
    public function getCatIcon()
    {
        return $this->cat_icon;
    }

    /**
     * Set cat_active
     * @param boolean $cat_active required
    **/
    public function setCatActive($cat_active = '')
    {
        if ($cat_active != '') {
            $this->cat_active = $cat_active;
        }
    }

    /**
     * Get cat_active
     * @return boolean
    **/
    public function getCatActive()
    {
        return $this->cat_active;
    }

    /**
     * Get a_my_parents
     * @return ??
    **/
    public function getMyParents()
    {
        return $this->a_my_parents;
    }

    /**
     * Get a_my_children
     * @return ??
    **/
    public function getMyChildren()
    {
        return $this->a_my_children;
    }
}
