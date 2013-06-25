<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\SobiCatItemsRelations
**/
class SobiCatItemsRelations
{
    /**
     * @var integer $catid
    **/
    private $catid;

    /**
     * @var integer $itemid
    **/
    private $itemid;

    /**
     * @var integer $ordering
    **/
    private $ordering;


    /**
     * Set catid
     * @param integer $catid
    **/
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
     * Set itemid
     * @param integer $itemid
    **/
    public function setItemid($itemid)
    {
        $this->itemid = $itemid;
    }

    /**
     * Get itemid
     * @return integer
    **/
    public function getItemid()
    {
        return $this->itemid;
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
}
