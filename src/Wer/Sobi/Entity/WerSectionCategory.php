<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerSectionCategory
**/
class WerSectionCategory
{
    /**
     * @var integer $scId
    **/
    private $scId;

    /**
     * @var Wer\Sobi\Entity\WerCategory
    **/
    private $scCategory;

    /**
     * @var Wer\Sobi\Entity\WerSection
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
     * @param Wer\Sobi\Entity\WerCategory $scCategory
    **/
    public function setScCategory(\Wer\Sobi\Entity\WerCategory $scCategory)
    {
        $this->scCategory = $scCategory;
    }

    /**
     * Get scCategory
     * @return Wer\Sobi\Entity\WerCategory
    **/
    public function getScCategory()
    {
        return $this->scCategory;
    }

    /**
     * Set scSection
     * @param Wer\Sobi\Entity\WerSection $scSection
    **/
    public function setScSection(\Wer\Sobi\Entity\WerSection $scSection)
    {
        $this->scSection = $scSection;
    }

    /**
     * Get scSection
     * @return Wer\Sobi\Entity\WerSection
    **/
    public function getScSection()
    {
        return $this->scSection;
    }
}
