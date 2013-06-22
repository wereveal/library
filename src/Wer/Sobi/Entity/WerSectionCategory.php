<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\SobiBundle\Entity\WerSectionCategory
**/
class WerSectionCategory
{
    /**
     * @var integer $scId
    **/
    private $scId;

    /**
     * @var Wer\SobiBundle\Entity\WerCategory
    **/
    private $scCategory;

    /**
     * @var Wer\SobiBundle\Entity\WerSection
    **/
    private $scSection;


    /**
     * Get scId
     * @return integer
    **/
    public function getScId()
    {
        return $this->scId;
    }

    /**
     * Set scCategory
     * @param Wer\SobiBundle\Entity\WerCategory $scCategory
    **/
    public function setScCategory(\Wer\SobiBundle\Entity\WerCategory $scCategory)
    {
        $this->scCategory = $scCategory;
    }

    /**
     * Get scCategory
     * @return Wer\SobiBundle\Entity\WerCategory
    **/
    public function getScCategory()
    {
        return $this->scCategory;
    }

    /**
     * Set scSection
     * @param Wer\SobiBundle\Entity\WerSection $scSection
    **/
    public function setScSection(\Wer\SobiBundle\Entity\WerSection $scSection)
    {
        $this->scSection = $scSection;
    }

    /**
     * Get scSection
     * @return Wer\SobiBundle\Entity\WerSection
    **/
    public function getScSection()
    {
        return $this->scSection;
    }
}
