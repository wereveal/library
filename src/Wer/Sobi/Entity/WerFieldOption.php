<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerFieldOption
**/
class WerFieldOption
{
    /**
     * @var integer $foId
    **/
    private $foId;

    /**
     * @var text $foFieldOption
    **/
    private $foFieldOption;

    /**
     * @var Wer\Sobi\Entity\WerField
    **/
    private $foField;


    /**
     * Get foId
     * @return integer
    **/
    public function getFoId()
    {
        return $this->foId;
    }

    /**
     * Set foFieldOption
     * @param text $foFieldOption
    **/
    public function setFoFieldOption($foFieldOption)
    {
        $this->foFieldOption = $foFieldOption;
    }

    /**
     * Get foFieldOption
     * @return text
    **/
    public function getFoFieldOption()
    {
        return $this->foFieldOption;
    }

    /**
     * Set foField
     * @param Wer\Sobi\Entity\WerField $foField
    **/
    public function setFoField(\Wer\Sobi\Entity\WerField $foField)
    {
        $this->foField = $foField;
    }

    /**
     * Get foField
     * @return Wer\Sobi\Entity\WerField
    **/
    public function getFoField()
    {
        return $this->foField;
    }
}
