<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\SobiBundle\Entity\SobiFieldsData
**/
class SobiFieldsData
{
    /**
     * @var integer $id
    **/
    private $id;

    /**
     * @var integer $fieldid
    **/
    private $fieldid;

    /**
     * @var text $dataTxt
    **/
    private $dataTxt;

    /**
     * @var boolean $dataBool
    **/
    private $dataBool;

    /**
     * @var integer $dataInt
    **/
    private $dataInt;

    /**
     * @var float $dataFloat
    **/
    private $dataFloat;

    /**
     * @var string $dataChar
    **/
    private $dataChar;

    /**
     * @var integer $itemid
    **/
    private $itemid;

    /**
     * @var datetime $expiration
    **/
    private $expiration;


    /**
     * Get id
     * @return integer
    **/
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fieldid
     * @param integer $fieldid
    **/
    public function setFieldid($fieldid)
    {
        $this->fieldid = $fieldid;
    }

    /**
     * Get fieldid
     * @return integer
    **/
    public function getFieldid()
    {
        return $this->fieldid;
    }

    /**
     * Set dataTxt
     * @param text $dataTxt
    **/
    public function setDataTxt($dataTxt)
    {
        $this->dataTxt = $dataTxt;
    }

    /**
     * Get dataTxt
     * @return text
    **/
    public function getDataTxt()
    {
        return $this->dataTxt;
    }

    /**
     * Set dataBool
     * @param boolean $dataBool
    **/
    public function setDataBool($dataBool)
    {
        $this->dataBool = $dataBool;
    }

    /**
     * Get dataBool
     * @return boolean
    **/
    public function getDataBool()
    {
        return $this->dataBool;
    }

    /**
     * Set dataInt
     * @param integer $dataInt
    **/
    public function setDataInt($dataInt)
    {
        $this->dataInt = $dataInt;
    }

    /**
     * Get dataInt
     * @return integer
    **/
    public function getDataInt()
    {
        return $this->dataInt;
    }

    /**
     * Set dataFloat
     * @param float $dataFloat
    **/
    public function setDataFloat($dataFloat)
    {
        $this->dataFloat = $dataFloat;
    }

    /**
     * Get dataFloat
     * @return float
    **/
    public function getDataFloat()
    {
        return $this->dataFloat;
    }

    /**
     * Set dataChar
     * @param string $dataChar
    **/
    public function setDataChar($dataChar)
    {
        $this->dataChar = $dataChar;
    }

    /**
     * Get dataChar
     * @return string
    **/
    public function getDataChar()
    {
        return $this->dataChar;
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
     * Set expiration
     * @param datetime $expiration
    **/
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * Get expiration
     * @return datetime
    **/
    public function getExpiration()
    {
        return $this->expiration;
    }
}
