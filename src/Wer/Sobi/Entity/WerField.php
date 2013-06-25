<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\WerField
**/
class WerField
{
    /**
     * @var integer $fieldId
    **/
    private $fieldId;

    /**
     * @var text $fieldName
    **/
    private $fieldName;

    /**
     * @var boolean $fieldEnabled
    **/
    private $fieldEnabled;

    /**
     * @var Wer\Sobi\Entity\WerFieldTypes
    **/
    private $fieldType;


    /**
     * Get fieldId
     * @return integer
    **/
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set fieldName
     * @param text $fieldName
    **/
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Get fieldName
     * @return text
    **/
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set fieldEnabled
     * @param boolean $fieldEnabled
    **/
    public function setFieldEnabled($fieldEnabled)
    {
        $this->fieldEnabled = $fieldEnabled;
    }

    /**
     * Get fieldEnabled
     * @return boolean
    **/
    public function getFieldEnabled()
    {
        return $this->fieldEnabled;
    }

    /**
     * Set fieldType
     * @param Wer\Sobi\Entity\WerFieldTypes $fieldType
    **/
    public function setFieldType(\Wer\Sobi\Entity\WerFieldTypes $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * Get fieldType
     * @return Wer\Sobi\Entity\WerFieldTypes
    **/
    public function getFieldType()
    {
        return $this->fieldType;
    }
}
