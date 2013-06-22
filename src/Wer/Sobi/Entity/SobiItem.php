<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\SobiBundle\Entity\SobiItem
**/
class SobiItem
{
    /**
     * @var integer $itemid
    **/
    private $itemid;

    /**
     * @var string $title
    **/
    private $title;

    /**
     * @var integer $hits
    **/
    private $hits;

    /**
     * @var integer $visits
    **/
    private $visits;

    /**
     * @var boolean $published
    **/
    private $published;

    /**
     * @var boolean $confirm
    **/
    private $confirm;

    /**
     * @var boolean $approved
    **/
    private $approved;

    /**
     * @var boolean $archived
    **/
    private $archived;

    /**
     * @var datetime $publishUp
    **/
    private $publishUp;

    /**
     * @var datetime $publishDown
    **/
    private $publishDown;

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
     * @var integer $owner
    **/
    private $owner;

    /**
     * @var string $icon
    **/
    private $icon;

    /**
     * @var string $image
    **/
    private $image;

    /**
     * @var string $background
    **/
    private $background;

    /**
     * @var text $options
    **/
    private $options;

    /**
     * @var text $params
    **/
    private $params;

    /**
     * @var string $ip
    **/
    private $ip;

    /**
     * @var datetime $lastUpdate
    **/
    private $lastUpdate;

    /**
     * @var integer $updatingUser
    **/
    private $updatingUser;

    /**
     * @var string $updatingIp
    **/
    private $updatingIp;

    /**
     * @var string $metakey
    **/
    private $metakey;

    /**
     * @var text $metadesc
    **/
    private $metadesc;


    /**
     * Get itemid
     * @return integer
    **/
    public function getItemid()
    {
        return $this->itemid;
    }

    /**
     * Set title
     * @param string $title
    **/
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     * @return string
    **/
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set hits
     * @param integer $hits
    **/
    public function setHits($hits)
    {
        $this->hits = $hits;
    }

    /**
     * Get hits
     * @return integer
    **/
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Set visits
     * @param integer $visits
    **/
    public function setVisits($visits)
    {
        $this->visits = $visits;
    }

    /**
     * Get visits
     * @return integer
    **/
    public function getVisits()
    {
        return $this->visits;
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
     * Set confirm
     * @param boolean $confirm
    **/
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;
    }

    /**
     * Get confirm
     * @return boolean
    **/
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * Set approved
     * @param boolean $approved
    **/
    public function setApproved($approved)
    {
        $this->approved = $approved;
    }

    /**
     * Get approved
     * @return boolean
    **/
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Set archived
     * @param boolean $archived
    **/
    public function setArchived($archived)
    {
        $this->archived = $archived;
    }

    /**
     * Get archived
     * @return boolean
    **/
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Set publishUp
     * @param datetime $publishUp
    **/
    public function setPublishUp($publishUp)
    {
        $this->publishUp = $publishUp;
    }

    /**
     * Get publishUp
     * @return datetime
    **/
    public function getPublishUp()
    {
        return $this->publishUp;
    }

    /**
     * Set publishDown
     * @param datetime $publishDown
    **/
    public function setPublishDown($publishDown)
    {
        $this->publishDown = $publishDown;
    }

    /**
     * Get publishDown
     * @return datetime
    **/
    public function getPublishDown()
    {
        return $this->publishDown;
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
     * Set owner
     * @param integer $owner
    **/
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner
     * @return integer
    **/
    public function getOwner()
    {
        return $this->owner;
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
     * Set background
     * @param string $background
    **/
    public function setBackground($background)
    {
        $this->background = $background;
    }

    /**
     * Get background
     * @return string
    **/
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Set options
     * @param text $options
    **/
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Get options
     * @return text
    **/
    public function getOptions()
    {
        return $this->options;
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
     * Set ip
     * @param string $ip
    **/
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * Get ip
     * @return string
    **/
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set lastUpdate
     * @param datetime $lastUpdate
    **/
    public function setLastUpdate($lastUpdate)
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * Get lastUpdate
     * @return datetime
    **/
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }

    /**
     * Set updatingUser
     * @param integer $updatingUser
    **/
    public function setUpdatingUser($updatingUser)
    {
        $this->updatingUser = $updatingUser;
    }

    /**
     * Get updatingUser
     * @return integer
    **/
    public function getUpdatingUser()
    {
        return $this->updatingUser;
    }

    /**
     * Set updatingIp
     * @param string $updatingIp
    **/
    public function setUpdatingIp($updatingIp)
    {
        $this->updatingIp = $updatingIp;
    }

    /**
     * Get updatingIp
     * @return string
    **/
    public function getUpdatingIp()
    {
        return $this->updatingIp;
    }

    /**
     * Set metakey
     * @param string $metakey
    **/
    public function setMetakey($metakey)
    {
        $this->metakey = $metakey;
    }

    /**
     * Get metakey
     * @return string
    **/
    public function getMetakey()
    {
        return $this->metakey;
    }

    /**
     * Set metadesc
     * @param text $metadesc
    **/
    public function setMetadesc($metadesc)
    {
        $this->metadesc = $metadesc;
    }

    /**
     * Get metadesc
     * @return text
    **/
    public function getMetadesc()
    {
        return $this->metadesc;
    }
}
