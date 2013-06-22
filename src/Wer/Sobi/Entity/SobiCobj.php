<?php

namespace Wer\SobiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\SobiBundle\Entity\SobiCobj
**/
class SobiCobj
{
    /**
     * @var integer $sid
    **/
    private $sid;

    /**
     * @var integer $cid
    **/
    private $cid;

    /**
     * @var integer $oid
    **/
    private $oid;

    /**
     * @var integer $cl
    **/
    private $cl;

    /**
     * @var string $slang
    **/
    private $slang;

    /**
     * @var integer $atime
    **/
    private $atime;

    /**
     * @var text $svars
    **/
    private $svars;

    /**
     * @var text $params
    **/
    private $params;

    /**
     * @var string $schecksum
    **/
    private $schecksum;


    /**
     * Set sid
     * @param integer $sid
    **/
    public function setSid($sid)
    {
        $this->sid = $sid;
    }

    /**
     * Get sid
     * @return integer
    **/
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set cid
     * @param integer $cid
    **/
    public function setCid($cid)
    {
        $this->cid = $cid;
    }

    /**
     * Get cid
     * @return integer
    **/
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * Set oid
     * @param integer $oid
    **/
    public function setOid($oid)
    {
        $this->oid = $oid;
    }

    /**
     * Get oid
     * @return integer
    **/
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * Set cl
     * @param integer $cl
    **/
    public function setCl($cl)
    {
        $this->cl = $cl;
    }

    /**
     * Get cl
     * @return integer
    **/
    public function getCl()
    {
        return $this->cl;
    }

    /**
     * Set slang
     * @param string $slang
    **/
    public function setSlang($slang)
    {
        $this->slang = $slang;
    }

    /**
     * Get slang
     * @return string
    **/
    public function getSlang()
    {
        return $this->slang;
    }

    /**
     * Set atime
     * @param integer $atime
    **/
    public function setAtime($atime)
    {
        $this->atime = $atime;
    }

    /**
     * Get atime
     * @return integer
    **/
    public function getAtime()
    {
        return $this->atime;
    }

    /**
     * Set svars
     * @param text $svars
    **/
    public function setSvars($svars)
    {
        $this->svars = $svars;
    }

    /**
     * Get svars
     * @return text
    **/
    public function getSvars()
    {
        return $this->svars;
    }

    /**
     * Set params
     * @param text $params
    **/
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Get params
     * @return text
    **/
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set schecksum
     * @param string $schecksum
    **/
    public function setSchecksum($schecksum)
    {
        $this->schecksum = $schecksum;
    }

    /**
     * Get schecksum
     * @return string
    **/
    public function getSchecksum()
    {
        return $this->schecksum;
    }
}
