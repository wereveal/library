<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Wer\SobiBundle\Entity\SobiCategories
**/
class SobiCategories
{
    /**
     * @var integer $catid
    **/
    private $catid;
    /**
     * @var string $name
    **/
    private $name;
    /**
     * @var string $image
    **/
    private $image;
    /**
     * @var string $imagePosition
    **/
    private $imagePosition;
    /**
     * @var text $description
    **/
    private $description;
    /**
     * @var string $introtext
    **/
    private $introtext;
    /**
     * @var boolean $published
    **/
    private $published;
    /**
     * @var integer $checkedOut
    **/
    private $checkedOut;
    /**
     * @var datetime $checkedOutTime
    **/
    private $checkedOutTime;
    /**
     * @var integer $ordering
    **/
    private $ordering;
    /**
     * @var boolean $access
    **/
    private $access;
    /**
     * @var integer $count
    **/
    private $count;
    /**
     * @var text $params
    **/
    private $params;
    /**
     * @var string $icon
    **/
    private $icon;
    /**
     * @var array $myParents
    **/
    private $myParents;
    /**
     * @var array $myChildren
    **/
    private $myChildren;
    /**
     * Constructor
    **/
    public function __construct()
    {
    }
    /**
     * Get catid
     * @return integer
    **/
    public function getCatid()
    {
        return $this->catid;
    }
    /**
     * Set name
     * @param string $name
    **/
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * Get name
     * @return string
    **/
    public function getName()
    {
        return $this->name;
    }
    /**
     * Set image
     * @param string $image
    **/
    public function setImage($image)
    {
        $this->image = $image;
    }
    /**
     * Get image
     * @return string
    **/
    public function getImage()
    {
        return $this->image;
    }
    /**
     * Set imagePosition
     * @param string $imagePosition
    **/
    public function setImagePosition( $imagePosition )
    {
        $this->imagePosition = $imagePosition;
    }
    /**
     * Get imagePosition
     * @return string
    **/
    public function getImagePosition()
    {
        return $this->imagePosition;
    }
    /**
     * Set description
     * @param text $description
    **/
    public function setDescription($description)
    {
        $this->description = $description;
    }
    /**
     * Get description
     * @return text
    **/
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Set introtext
     * @param string $introtext
    **/
    public function setIntrotext($introtext)
    {
        $this->introtext = $introtext;
    }
    /**
     * Get introtext
     * @return string
    **/
    public function getIntrotext()
    {
        return $this->introtext;
    }
    /**
     * Set published
     * @param boolean $published
    **/
    public function setPublished($published)
    {
        $this->published = $published;
    }
    /**
     * Get published
     * @return boolean
    **/
    public function getPublished()
    {
        return $this->published;
    }
    /**
     * Set checkedOut
     * @param integer $checkedOut
    **/
    public function setCheckedOut($checkedOut)
    {
        $this->checkedOut = $checkedOut;
    }
    /**
     * Get checkedOut
     * @return integer
    **/
    public function getCheckedOut()
    {
        return $this->checkedOut;
    }
    /**
     * Set checkedOutTime
     * @param datetime $checkedOutTime
    **/
    public function setCheckedOutTime($checkedOutTime)
    {
        $this->checkedOutTime = $checkedOutTime;
    }
    /**
     * Get checkedOutTime
     * @return datetime
    **/
    public function getCheckedOutTime()
    {
        return $this->checkedOutTime;
    }
    /**
     * Set ordering
     * @param integer $ordering
    **/
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }
    /**
     * Get ordering
     * @return integer
    **/
    public function getOrdering()
    {
        return $this->ordering;
    }
    /**
     * Set access
     * @param boolean $access
    **/
    public function setAccess($access)
    {
        $this->access = $access;
    }
    /**
     * Get access
     * @return boolean
    **/
    public function getAccess()
    {
        return $this->access;
    }
    /**
     * Set count
     * @param integer $count
    **/
    public function setCount($count)
    {
        $this->count = $count;
    }
    /**
     * Get count
     * @return integer
    **/
    public function getCount()
    {
        return $this->count;
    }
    /**
     * Set params
     * @param text $params
    **/
    public function setParams($params)
    {
        $this->params = $params;
    }
    /**
     * Get params
     * @return text
    **/
    public function getParams()
    {
        return $this->params;
    }
    /**
     * Set icon
     * @param string $icon
    **/
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }
    /**
     * Get icon
     * @return string
    **/
    public function getIcon()
    {
        return $this->icon;
    }
    /**
     * Get the array myChildren
     * @return array
    **/
    public function getMyChildren()
    {
        return $this->myChildren;
    }
    /**
     * Get the array myParents
     * @return array
    **/
    public function getMyParents()
    {
        return $this->myParents;
    }
}
