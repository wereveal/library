<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Wer\Sobi\Entity\SobiCatsRelations
**/
class SobiCatsRelations {
    /**
     * @var integer $catid
    **/
    private $catid;
    /**
     * @var integer $parentid
    **/
    private $parentid;
    /**
     * Set catid
     * @param integer $catid
    **/
    private $myParents;
    public function __construct()
    {
        $this->myParents = new ArrayCollection;
    }
    public function setCatid($catid)
    {
        $this->catid = $catid;
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
     * Set parentid
     * @param integer $parentid
    **/
    public function setParentid($parentid)
    {
        $this->parentid = $parentid;
    }
    /**
     * Get parentid
     * @return integer
    **/
    public function getParentid()
    {
        return $this->parentid;
    }
}
