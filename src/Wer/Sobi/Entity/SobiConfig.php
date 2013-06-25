<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\SobiConfig
**/
class SobiConfig
{
    /**
     * @var string $configkey
    **/
    private $configkey;

    /**
     * @var string $sobi2section
    **/
    private $sobi2section;

    /**
     * @var text $configvalue
    **/
    private $configvalue;

    /**
     * @var text $description
    **/
    private $description;


    /**
     * Set configkey
     * @param string $configkey
    **/
    public function setConfigkey($configkey)
    {
        $this->configkey = $configkey;
    }

    /**
     * Get configkey
     * @return string
    **/
    public function getConfigkey()
    {
        return $this->configkey;
    }

    /**
     * Set sobi2section
     * @param string $sobi2section
    **/
    public function setSobi2section($sobi2section)
    {
        $this->sobi2section = $sobi2section;
    }

    /**
     * Get sobi2section
     * @return string
    **/
    public function getSobi2section()
    {
        return $this->sobi2section;
    }

    /**
     * Set configvalue
     * @param text $configvalue
    **/
    public function setConfigvalue($configvalue)
    {
        $this->configvalue = $configvalue;
    }

    /**
     * Get configvalue
     * @return text
    **/
    public function getConfigvalue()
    {
        return $this->configvalue;
    }

    /**
     * Set description
     * @param text $description
    **/
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     * @return text
    **/
    public function getDescription()
    {
        return $this->description;
    }
}
