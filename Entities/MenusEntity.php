<?php
/**
 * @brief     Basic accessors for a menu entity.
 * @ingroup   lib_entities
 * @file      Ritc/Library/Entities/MenusEntity.php
 * @namespace Ritc\Library\Entities
 * @author    William E Reveal
 * @version   1.0.0
 * @date      2016-02-23 11:09:18
 * @note <b>SQL for table<b>
 * - MySQL      - resources/sql/mysql/menus_mysql.sql
 * - PostgreSQL - resources/sql/postgresql/menus_pg.sql
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class MenusEntity
 * @class   MenusEntity
 * @package Ritc\Library\Entities
 */
class MenusEntity implements EntityInterface
{
    /** @var int */
    private $menu_id;
    /** @var int */
    private $menu_page_id;
    /** @var int */
    private $menu_parent_id;
    /** @var string */
    private $menu_name;
    /** @var string */
    private $menu_css;
    /** @var int */
    private $menu_level;
    /** @var int */
    private $menu_order;
    /** @var int */
    private $menu_active;

    /**
     * Returns all the record values.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'menu_id'        => $this->menu_id,
            'menu_page_id'   => $this->menu_page_id,
            'menu_parent_id' => $this->menu_parent_id,
            'menu_name'      => $this->menu_name,
            'menu_css'       => $this->menu_css,
            'menu_level'     => $this->menu_level,
            'menu_order'     => $this->menu_order,
            'menu_active'    => $this->menu_active
        );
    }

    /**
     * Sets all the properties for the record in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        $a_default_values = [
            'menu_id'        => 0,
            'menu_page_id'   => 0,
            'menu_parent_id' => 0,
            'menu_name'      => 'Fred',
            'menu_css'       => 'menu-item',
            'menu_level'     => 1,
            'menu_order'     => 0,
            'menu_active'    => 1
        ];
        foreach ($a_default_values as $key_name => $default_value) {
            if (array_key_exists($key_name, $a_entity)) {
                $this->$key_name = $a_entity[$key_name];
            }
            else {
                $this->$key_name = $default_value;
            }
        }
        return true;
    }

    /**
     * @return int
     */
    public function getMenuId()
    {
        return $this->menu_id;
    }

    /**
     * @param int $menu_id
     */
    public function setMenuId($menu_id)
    {
        $this->menu_id = $menu_id;
    }

    /**
     * @return int
     */
    public function getMenuPageId()
    {
        return $this->menu_page_id;
    }

    /**
     * @param int $menu_page_id
     */
    public function setMenuPageId($menu_page_id)
    {
        $this->menu_page_id = $menu_page_id;
    }

    /**
     * @return int
     */
    public function getMenuParentId()
    {
        return $this->menu_parent_id;
    }

    /**
     * @param int $menu_parent_id
     */
    public function setMenuParentId($menu_parent_id)
    {
        $this->menu_parent_id = $menu_parent_id;
    }

    /**
     * @return string
     */
    public function getMenuName()
    {
        return $this->menu_name;
    }

    /**
     * @param string $menu_name
     */
    public function setMenuName($menu_name)
    {
        $this->menu_name = $menu_name;
    }

    /**
     * @return string
     */
    public function getMenuCss()
    {
        return $this->menu_css;
    }

    /**
     * @param string $menu_css
     */
    public function setMenuCss($menu_css)
    {
        $this->menu_css = $menu_css;
    }

    /**
     * @return int
     */
    public function getMenuOrder()
    {
        return $this->menu_order;
    }

    /**
     * @param int $menu_order
     */
    public function setMenuOrder($menu_order)
    {
        $this->menu_order = $menu_order;
    }

    /**
     * @return int
     */
    public function getMenuActive()
    {
        return $this->menu_active;
    }

    /**
     * @param int $menu_active
     */
    public function setMenuActive($menu_active)
    {
        $this->menu_active = $menu_active;
    }

    /**
     * @return int
     */
    public function getMenuLevel()
    {
        return $this->menu_level;
    }

    /**
     * @param int $menu_level
     */
    public function setMenuLevel($menu_level)
    {
        $this->menu_level = $menu_level;
    }

}
