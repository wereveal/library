<?php
/**
 * Class PagesEntity
 * @package Ritc_Library
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class PagesEntity.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-beta.2
 * @date    2021-11-26 16:20:57
 * @change_log
 * - v1.0.0-beta.2 - updated for php8                           - 2021-11-26 wer
 * - v1.0.0-beta.1 - Initial version                            - 10/30/2015 wer
 */
class PagesEntity implements EntityInterface
{

    /** @var array $a_entity */
    private array $a_entity;
    /** @var string $page_base_url */
    private string $page_base_url;
    /** @var string $page_charset */
    private string $page_charset;
    /** @var string $page_description */
    private string $page_description;
    /** @var int $page_id */
    private int $page_id;
    /** @var string $page_lang */
    private string $page_lang;
    /** @var string $page_title */
    private string $page_title;
    /** @var string $page_type */
    private string $page_type;
    /** @var string $page_url */
    private string $page_url;

    /**
     * @return array
     */
    public function getAllProperties():array
    {
        return $this->a_entity;
    }

    /**
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array()):bool
    {
        $a_required_keys = [
            'page_base_url',
            'page_charset',
            'page_description',
            'page_id',
            'page_lang',
            'page_title',
            'page_type',
            'page_url'
        ];
        $a_entity = Arrays::createRequiredPairs($a_entity, $a_required_keys, true);
        $this->a_entity = $a_entity;
        foreach ($a_entity as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getPageBaseUrl():string
    {
        return $this->page_base_url;
    }

    /**
     * @param string $page_base_url
     */
    public function setPageBaseUrl(string $page_base_url):void
    {
        $this->page_base_url = $page_base_url;
    }

    /**
     * @return string
     */
    public function getPageCharset():string
    {
        return $this->page_charset;
    }

    /**
     * @param string $page_charset
     */
    public function setPageCharset(string $page_charset):void
    {
        $this->page_charset = $page_charset;
    }

    /**
     * @return string
     */
    public function getPageDescription():string
    {
        return $this->page_description;
    }

    /**
     * @param string $page_description
     */
    public function setPageDescription(string $page_description):void
    {
        $this->page_description = $page_description;
    }

    /**
     * @return int
     */
    public function getPageId():int
    {
        return $this->page_id;
    }

    /**
     * @param int $page_id
     */
    public function setPageId(int $page_id = -1):void
    {
        if ($page_id !== -1) {
            $this->page_id = $page_id;
        }
    }

    /**
     * @return string
     */
    public function getPageLang():string
    {
        return $this->page_lang;
    }

    /**
     * @param string $page_lang
     */
    public function setPageLang(string $page_lang):void
    {
        $this->page_lang = $page_lang;
    }

    /**
     * @return string
     */
    public function getPageTitle():string
    {
        return $this->page_title;
    }

    /**
     * @param string $page_title
     */
    public function setPageTitle(string $page_title):void
    {
        $this->page_title = $page_title;
    }

    /**
     * @return string
     */
    public function getPageType():string
    {
        return $this->page_type;
    }

    /**
     * @param string $page_type
     */
    public function setPageType(string $page_type):void
    {
        $this->page_type = $page_type;
    }

    /**
     * @return string
     */
    public function getPageUrl():string
    {
        return $this->page_url;
    }

    /**
     * @param string $page_url
     */
    public function setPageUrl(string $page_url):void
    {
        $this->page_url = $page_url;
    }
}
