<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
    Wer\GuideBundle\Entity\WerFieldOption
**/
class WerFieldOption
{
    /**
     * @var integer $fo_Id
    **/
    private $fo_id;

    /**
     * @var text $fo_field_option
    **/
    private $fo_field_option;

    /**
     * @var Wer\GuideBundle\Entity\WerField
    **/
    private $fo_field;


    /**
     * Get fo_id
     * @return integer
    **/
    public function getFoId() {
        return $this->fo_id;
    }

    /**
     * Set fo_field_option
     * @param text $fo_field_option
    **/
    public function setFoFieldOption($fo_field_option = '')
    {
        $this->fo_field_option = $fo_field_option;
    }

    /**
     * Get fo_field_option
     * @return text
    **/
    public function getFoFieldOption() {
        return $this->fo_field_option;
    }

    /**
     * Set fo_field
     * @param Wer\GuideBundle\Entity\WerField $fo_field
    **/
    public function setFoField(\Wer\GuideBundle\Entity\WerField $fo_field)
    {
        $this->fo_field = $fo_field;
    }

    /**
     * Get fo_field
     * @return Wer\GuideBundle\Entity\WerField
    **/
    public function getFoField()
    {
        return $this->fo_field;
    }
}