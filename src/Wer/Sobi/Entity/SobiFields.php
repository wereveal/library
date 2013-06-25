<?php

namespace Wer\Sobi\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wer\Sobi\Entity\SobiFields
**/
class SobiFields
{
    /**
     * @var integer $fieldid
    **/
    private $fieldid;

    /**
     * @var integer $fieldtype
    **/
    private $fieldtype;

    /**
     * @var boolean $wysiwyg
    **/
    private $wysiwyg;

    /**
     * @var text $fielddescription
    **/
    private $fielddescription;

    /**
     * @var text $explanation
    **/
    private $explanation;

    /**
     * @var boolean $isFree
    **/
    private $isFree;

    /**
     * @var float $payment
    **/
    private $payment;

    /**
     * @var integer $fieldchars
    **/
    private $fieldchars;

    /**
     * @var integer $fieldrows
    **/
    private $fieldrows;

    /**
     * @var integer $fieldcolumns
    **/
    private $fieldcolumns;

    /**
     * @var integer $preferredSize
    **/
    private $preferredSize;

    /**
     * @var text $cssclass
    **/
    private $cssclass;

    /**
     * @var boolean $enabled
    **/
    private $enabled;

    /**
     * @var boolean $iseditable
    **/
    private $iseditable;

    /**
     * @var boolean $isRequired
    **/
    private $isRequired;

    /**
     * @var boolean $inPromoted
    **/
    private $inPromoted;

    /**
     * @var boolean $inVcard
    **/
    private $inVcard;

    /**
     * @var boolean $inDetails
    **/
    private $inDetails;

    /**
     * @var integer $position
    **/
    private $position;

    /**
     * @var integer $inSearch
    **/
    private $inSearch;

    /**
     * @var boolean $withLabel
    **/
    private $withLabel;

    /**
     * @var boolean $inNewline
    **/
    private $inNewline;

    /**
     * @var integer $isurl
    **/
    private $isurl;

    /**
     * @var integer $checkedOut
    **/
    private $checkedOut;

    /**
     * @var datetime $checkedOutTime
    **/
    private $checkedOutTime;

    /**
     * @var boolean $displayed
    **/
    private $displayed;


    /**
     * Get fieldid
     * @return integer
    **/
    public function getFieldid()
    {
        return $this->fieldid;
    }

    /**
     * Set fieldtype
     * @param integer $fieldtype
    **/
    public function setFieldtype($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    /**
     * Get fieldtype
     * @return integer
    **/
    public function getFieldtype()
    {
        return $this->fieldtype;
    }

    /**
     * Set wysiwyg
     * @param boolean $wysiwyg
    **/
    public function setWysiwyg($wysiwyg)
    {
        $this->wysiwyg = $wysiwyg;
    }

    /**
     * Get wysiwyg
     * @return boolean
    **/
    public function getWysiwyg()
    {
        return $this->wysiwyg;
    }

    /**
     * Set fielddescription
     * @param text $fielddescription
    **/
    public function setFielddescription($fielddescription)
    {
        $this->fielddescription = $fielddescription;
    }

    /**
     * Get fielddescription
     * @return text
    **/
    public function getFielddescription()
    {
        return $this->fielddescription;
    }

    /**
     * Set explanation
     * @param text $explanation
    **/
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;
    }

    /**
     * Get explanation
     * @return text
    **/
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Set isFree
     * @param boolean $isFree
    **/
    public function setIsFree($isFree)
    {
        $this->isFree = $isFree;
    }

    /**
     * Get isFree
     * @return boolean
    **/
    public function getIsFree()
    {
        return $this->isFree;
    }

    /**
     * Set payment
     * @param float $payment
    **/
    public function setPayment($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get payment
     * @return float
    **/
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set fieldchars
     * @param integer $fieldchars
    **/
    public function setFieldchars($fieldchars)
    {
        $this->fieldchars = $fieldchars;
    }

    /**
     * Get fieldchars
     * @return integer
    **/
    public function getFieldchars()
    {
        return $this->fieldchars;
    }

    /**
     * Set fieldrows
     * @param integer $fieldrows
    **/
    public function setFieldrows($fieldrows)
    {
        $this->fieldrows = $fieldrows;
    }

    /**
     * Get fieldrows
     * @return integer
    **/
    public function getFieldrows()
    {
        return $this->fieldrows;
    }

    /**
     * Set fieldcolumns
     * @param integer $fieldcolumns
    **/
    public function setFieldcolumns($fieldcolumns)
    {
        $this->fieldcolumns = $fieldcolumns;
    }

    /**
     * Get fieldcolumns
     * @return integer
    **/
    public function getFieldcolumns()
    {
        return $this->fieldcolumns;
    }

    /**
     * Set preferredSize
     * @param integer $preferredSize
    **/
    public function setPreferredSize($preferredSize)
    {
        $this->preferredSize = $preferredSize;
    }

    /**
     * Get preferredSize
     * @return integer
    **/
    public function getPreferredSize()
    {
        return $this->preferredSize;
    }

    /**
     * Set cssclass
     * @param text $cssclass
    **/
    public function setCssclass($cssclass)
    {
        $this->cssclass = $cssclass;
    }

    /**
     * Get cssclass
     * @return text
    **/
    public function getCssclass()
    {
        return $this->cssclass;
    }

    /**
     * Set enabled
     * @param boolean $enabled
    **/
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     * @return boolean
    **/
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set iseditable
     * @param boolean $iseditable
    **/
    public function setIseditable($iseditable)
    {
        $this->iseditable = $iseditable;
    }

    /**
     * Get iseditable
     * @return boolean
    **/
    public function getIseditable()
    {
        return $this->iseditable;
    }

    /**
     * Set isRequired
     * @param boolean $isRequired
    **/
    public function setIsRequired($isRequired)
    {
        $this->isRequired = $isRequired;
    }

    /**
     * Get isRequired
     * @return boolean
    **/
    public function getIsRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set inPromoted
     * @param boolean $inPromoted
    **/
    public function setInPromoted($inPromoted)
    {
        $this->inPromoted = $inPromoted;
    }

    /**
     * Get inPromoted
     * @return boolean
    **/
    public function getInPromoted()
    {
        return $this->inPromoted;
    }

    /**
     * Set inVcard
     * @param boolean $inVcard
    **/
    public function setInVcard($inVcard)
    {
        $this->inVcard = $inVcard;
    }

    /**
     * Get inVcard
     * @return boolean
    **/
    public function getInVcard()
    {
        return $this->inVcard;
    }

    /**
     * Set inDetails
     * @param boolean $inDetails
    **/
    public function setInDetails($inDetails)
    {
        $this->inDetails = $inDetails;
    }

    /**
     * Get inDetails
     * @return boolean
    **/
    public function getInDetails()
    {
        return $this->inDetails;
    }

    /**
     * Set position
     * @param integer $position
    **/
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     * @return integer
    **/
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set inSearch
     * @param integer $inSearch
    **/
    public function setInSearch($inSearch)
    {
        $this->inSearch = $inSearch;
    }

    /**
     * Get inSearch
     * @return integer
    **/
    public function getInSearch()
    {
        return $this->inSearch;
    }

    /**
     * Set withLabel
     * @param boolean $withLabel
    **/
    public function setWithLabel($withLabel)
    {
        $this->withLabel = $withLabel;
    }

    /**
     * Get withLabel
     * @return boolean
    **/
    public function getWithLabel()
    {
        return $this->withLabel;
    }

    /**
     * Set inNewline
     * @param boolean $inNewline
    **/
    public function setInNewline($inNewline)
    {
        $this->inNewline = $inNewline;
    }

    /**
     * Get inNewline
     * @return boolean
    **/
    public function getInNewline()
    {
        return $this->inNewline;
    }

    /**
     * Set isurl
     * @param integer $isurl
    **/
    public function setIsurl($isurl)
    {
        $this->isurl = $isurl;
    }

    /**
     * Get isurl
     * @return integer
    **/
    public function getIsurl()
    {
        return $this->isurl;
    }

    /**
     * Set checkedOut
     * @param integer $checkedOut
    **/
    public function setCheckedOut($checkedOut)
    {
        $this->checkedOut = $checkedOut;
    }

    /**
     * Get checkedOut
     * @return integer
    **/
    public function getCheckedOut()
    {
        return $this->checkedOut;
    }

    /**
     * Set checkedOutTime
     * @param datetime $checkedOutTime
    **/
    public function setCheckedOutTime($checkedOutTime)
    {
        $this->checkedOutTime = $checkedOutTime;
    }

    /**
     * Get checkedOutTime
     * @return datetime
    **/
    public function getCheckedOutTime()
    {
        return $this->checkedOutTime;
    }

    /**
     * Set displayed
     * @param boolean $displayed
    **/
    public function setDisplayed($displayed)
    {
        $this->displayed = $displayed;
    }

    /**
     * Get displayed
     * @return boolean
    **/
    public function getDisplayed()
    {
        return $this->displayed;
    }
}
