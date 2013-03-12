<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\GuideBundle\Entity\WerField
**/
class WerField
{
    /**
     * @var integer $field_id
    **/
    private $field_id;

    /**
     * @var int
    **/
    private $field_type_id;

    /**
     * @var text $field_name
    **/
    private $field_name;

    /**
     * @var text $field_short_description
    **/
    private $field_short_description;

    /**
     * @var text $field_description
    **/
    private $field_description;

    /**
     * @var boolean $field_enabled
    **/
    private $field_enabled;

    /**
     * @var int $field_old_field_id
    **/
    private $field_old_field_id;

    /**
     * Get field_id
     * @return integer
    **/
    public function getFieldId()
    {
        return $this->field_id;
    }

    /**
     * Set field_name
     * @param text $field_name required
    **/
    public function setFieldName($field_name = '')
    {
        if ($field_name != '') {
            $this->field_name = $field_name;
        }
    }

    /**
     * Get field_name
     * @return text
    **/
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     * Set field_enabled
     * @param boolean $field_enabled required
    **/
    public function setFieldEnabled($field_enabled = '')
    {
        if ($field_enabled != '') {
            $this->field_enabled = $field_enabled;
        }
    }

    /**
     * Get field_enabled
     * @return boolean
    **/
    public function getFieldEnabled()
    {
        return $this->field_enabled;
    }

    /**
     * Set field_type
     * @param int $field_type_id
    **/
    public function setFieldTypeId($field_type_id = '')
    {
        $this->field_type_id = $field_type_id;
    }

    /**
     * Get field_type
     * @return int
    **/
    public function getFieldTypeId()
    {
        return $this->field_type_id;
    }
}
