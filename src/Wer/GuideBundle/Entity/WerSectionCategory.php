<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\GuideBundle\Entity\WerSectionCategory
**/
class WerSectionCategory
{
    /**
     * @var integer $sc_id
    **/
    private $sc_id;

    /**
     * @var Wer\GuideBundle\Entity\WerCategory
    **/
    private $sc_category;

    /**
     * @var Wer\GuideBundle\Entity\WerSection
    **/
    private $sc_section;


    /**
     * Get sc_id
     * @return integer
    **/
    public function getScId()
    {
        return $this->sc_id;
    }

    /**
     * Set sc_category
     * @param Wer\GuideBundle\Entity\WerCategory $sc_category
    **/
    public function setScCategory(\Wer\GuideBundle\Entity\WerCategory $sc_category)
    {
        $this->sc_category = $sc_category;
    }

    /**
     * Get sc_category
     * @return Wer\GuideBundle\Entity\WerCategory
    **/
    public function getScCategory()
    {
        return $this->sc_category;
    }

    /**
     * Set sc_section
     * @param Wer\GuideBundle\Entity\WerSection $sc_section
    **/
    public function setScSection(\Wer\GuideBundle\Entity\WerSection $sc_section)
    {
        $this->sc_section = $sc_section;
    }

    /**
     * Get sc_section
     * @return Wer\GuideBundle\Entity\WerSection
    **/
    public function getScSection()
    {
        return $this->sc_section;
    }
}