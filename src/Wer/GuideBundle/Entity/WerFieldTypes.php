<?php
namespace Wer\GuideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\GuideBundle\Entity\WerFieldTypes
**/
class WerFieldTypes
{
    /**
     * @var integer $ft_id
    **/
    private $ft_id;

    /**
     * @var text $ft_name
    **/
    private $ft_name;

    /**
     * @var text $ft_type
    **/
    private $ft_type;


    /**
     * Get ft_id
     * @return integer
    **/
    public function getFtId()
    {
        return $this->ft_id;
    }

    /**
     * Set ft_name
     * @param text $ft_name
    **/
    public function setFtName($ft_name = '')
    {
        $this->ft_name = $ft_name;
    }

    /**
     * Get ft_name
     * @return text
    **/
    public function getFtName()
    {
        return $this->ft_name;
    }

    /**
     * Set ft_type
     * @param text $ft_type
    **/
    public function setFtType($ft_type = '')
    {
        $this->ft_type = $ft_type;
    }

    /**
     * Get ft_type
     * @return text
    **/
    public function getFtType()
    {
        return $this->ft_type;
    }
}