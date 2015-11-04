<?php
/**
 *  @brief An entity class for the page table.
 *  @details This Provides data needed to generate the html, e.g. title,
 *      description, file type, etc that would be in the <head> part of the page.
 *  @file PageEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class PageEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β1
 *  @date 2015-10-30 08:14:03
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β1 - Initial version - 10/30/2015 wer
 *  </pre>
 *  @note  <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/page_mysql.sql
 *      PostgreSQL - resources/sql/postgresql/page_pg.sql</pre>
 */

namespace Ritc\Library\Entities;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\EntityInterface;

class PageEntity implements EntityInterface
{

    private $a_entity;
    private $page_base_url;
    private $page_charset;
    private $page_description;
    private $page_id;
    private $page_lang;
    private $page_title;
    private $page_type;
    private $page_url;

    /**
     * @return array
     */
    public function getAllProperties()
    {
        return $this->a_entity;
    }
    /**
     * @param array $a_entity
     */
    public function setAllProperties(array $a_entity = array())
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
    }
    /**
     * @return string
     */
    public function getPageBaseUrl()
    {
        return $this->page_base_url;
    }

    /**
     * @param string $page_base_url
     */
    public function setPageBaseUrl($page_base_url)
    {
        $this->page_base_url = $page_base_url;
    }

    /**
     * @return string
     */
    public function getPageCharset()
    {
        return $this->page_charset;
    }

    /**
     * @param string $page_charset
     */
    public function setPageCharset($page_charset)
    {
        $this->page_charset = $page_charset;
    }

    /**
     * @return string
     */
    public function getPageDescription()
    {
        return $this->page_description;
    }

    /**
     * @param string $page_description
     */
    public function setPageDescription($page_description)
    {
        $this->page_description = $page_description;
    }

    /**
     * @return int
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * @param int $page_id
     */
    public function setPageId($page_id = -1)
    {
        if ($page_id != -1) {
            $this->page_id = $page_id;
        }
    }

    /**
     * @return string
     */
    public function getPageLang()
    {
        return $this->page_lang;
    }

    /**
     * @param string $page_lang
     */
    public function setPageLang($page_lang)
    {
        $this->page_lang = $page_lang;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->page_title;
    }

    /**
     * @param string $page_title
     */
    public function setPageTitle($page_title)
    {
        $this->page_title = $page_title;
    }

    /**
     * @return string
     */
    public function getPageType()
    {
        return $this->page_type;
    }

    /**
     * @param string $page_type
     */
    public function setPageType($page_type)
    {
        $this->page_type = $page_type;
    }

    /**
     * @return string
     */
    public function getPageUrl()
    {
        return $this->page_url;
    }

    /**
     * @param string $page_url
     */
    public function setPageUrl($page_url)
    {
        $this->page_url = $page_url;
    }

}
