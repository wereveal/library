<?php
/**
 * Class MenusEntity
 * @package Ritc_Library
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class MenusEntity
 *
 * @author  William E Reveal
 * @version 2.0.0
 * @date    2021-11-26 16:19:44
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-26 wer
 * - v1.0.0 - Initial Version                                   - 2016-02-23 wer
 */
class MenusEntity implements EntityInterface
{
    /** @var int */
    private int $menu_id;
    /** @var int */
    private int $menu_page_id;
    /** @var int */
    private int $menu_parent_id;
    /** @var string */
    private string $menu_name;
    /** @var string */
    private string $menu_css;
    /** @var int */
    private int $menu_level;
    /** @var int */
    private int $menu_order;
    /** @var int */
    private int $menu_active;

    /**
     * Returns all the record values.
     *
     * @return array
     */
    public function getAllProperties():array
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
     *
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array()):bool
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
    public function getMenuId():int
    {
        return $this->menu_id;
    }

    /**
     * @param int $menu_id
     */
    public function setMenuId(int $menu_id):void
    {
        $this->menu_id = $menu_id;
    }

    /**
     * @return int
     */
    public function getMenuPageId():int
    {
        return $this->menu_page_id;
    }

    /**
     * @param int $menu_page_id
     */
    public function setMenuPageId(int $menu_page_id):void
    {
        $this->menu_page_id = $menu_page_id;
    }

    /**
     * @return int
     */
    public function getMenuParentId():int
    {
        return $this->menu_parent_id;
    }

    /**
     * @param int $menu_parent_id
     */
    public function setMenuParentId(int $menu_parent_id):void
    {
        $this->menu_parent_id = $menu_parent_id;
    }

    /**
     * @return string
     */
    public function getMenuName():string
    {
        return $this->menu_name;
    }

    /**
     * @param string $menu_name
     */
    public function setMenuName(string $menu_name):void
    {
        $this->menu_name = $menu_name;
    }

    /**
     * @return string
     */
    public function getMenuCss():string
    {
        return $this->menu_css;
    }

    /**
     * @param string $menu_css
     */
    public function setMenuCss(string $menu_css):void
    {
        $this->menu_css = $menu_css;
    }

    /**
     * @return int
     */
    public function getMenuOrder():int
    {
        return $this->menu_order;
    }

    /**
     * @param int $menu_order
     */
    public function setMenuOrder(int $menu_order):void
    {
        $this->menu_order = $menu_order;
    }

    /**
     * @return int
     */
    public function getMenuActive():int
    {
        return $this->menu_active;
    }

    /**
     * @param int $menu_active
     */
    public function setMenuActive(int $menu_active):void
    {
        $this->menu_active = $menu_active;
    }

    /**
     * @return int
     */
    public function getMenuLevel():int
    {
        return $this->menu_level;
    }

    /**
     * @param int $menu_level
     */
    public function setMenuLevel(int $menu_level):void
    {
        $this->menu_level = $menu_level;
    }

}
