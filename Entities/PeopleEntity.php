<?php
/**
 *  @brief     An entity class for People.
 *  @details   It needs to be noted that this reflects the fact that
 *             a user entity consists of data that comes from more than one
 *             database table.
 *  @ingroup   ritc_library lib_entities
 *  @file      PeopleEntity.php
 *  @namespace Ritc\Library\Entities
 *  @class     PeopleEntity
 *  @author    William E Reveal <bill@revealitconsulting.com>
 *  @version   1.1.0
 *  @date      2015-09-03 15:31:12
 *  @note  <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/page_mysql.sql
 *      PostgreSQL - resources/sql/postgresql/page_pg.sql</pre>
 *  @note <pre><b>Change Log</b>
 *      v1.1.0 - changed is_default to is_immutable to be more descriptive - 09/03/2015 wer
 *      v1.0.0 - finalized       - 07/29/2015 wer
 *      v0.1.0 - Initial version - 09/11/2014 wer
 *  </pre>
 **/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class PeopleEntity implements EntityInterface
{
    private $people_id = '';
    private $login_id = '';
    private $real_name = '';
    private $short_name = '';
    private $password = '';
    private $is_logged_in = 0;
    private $bad_login_count = 0;
    private $bad_login_ts = 0;
    private $is_active = 0;
    private $is_immutable = 0;
    private $created_on = 0;

    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'people_id'       => $this->people_id,
            'login_id'        => $this->login_id,
            'real_name'       => $this->real_name,
            'short_name'      => $this->short_name,
            'password'        => $this->password,
            'is_logged_in'    => $this->is_logged_in,
            'bad_login_count' => $this->bad_login_count,
            'bad_login_ts'    => $this->bad_login_ts,
            'is_active'       => $this->is_active,
            'is_immutable'    => $this->is_immutable,
            'created_on'      => $this->created_on,
        );
    }
    /**
     * Sets all the properties for the entity in one step.
     * @param array $a_entity
     * @return bool
     */
    public function setAllProperties(array $a_entity = array())
    {
        $a_needed_keys = array(
            'people_id'       => 0,
            'login_id'        => 0,
            'real_name'       => '',
            'short_name'      => '',
            'password'        => '',
            'is_logged_in'    => 0,
            'bad_login_count' => -1,
            'bad_login_ts'    => 0,
            'is_active'       => 0,
            'is_immutable'      => 0,
            'created_on'      => 0,
        );
        foreach ($a_needed_keys as $key_name => $default_value) {
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
     * @param int $bad_login_count
     */
    public function setBadLoginCount($bad_login_count)
    {
        $this->bad_login_count = $bad_login_count;
    }
    /**
     * @return int
     */
    public function getBadLoginCount()
    {
        return $this->bad_login_count;
    }
    /**
     * @param int $bad_login_ts
     */
    public function setBadLoginTs($bad_login_ts)
    {
        $this->bad_login_ts = $bad_login_ts;
    }
    /**
     * @return int
     */
    public function getBadLoginTs()
    {
        return $this->bad_login_ts;
    }
    /**
     * @param int $created_on
     */
    public function setCreatedOn($created_on)
    {
        $this->created_on = $created_on;
    }
    /**
     * @return int
     */
    public function getCreatedOn()
    {
        return $this->created_on;
    }
    /**
     * @param int $is_active
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
    }
    /**
     * @return int
     */
    public function getIsActive()
    {
        return $this->is_active;
    }
    /**
     * @param int $is_immutable
     */
    public function setIsImmutable($is_immutable)
    {
        $this->$is_immutable = $is_immutable;
    }
    /**
     * @return int
     */
    public function getIsImmutable()
    {
        return $this->is_immutable;
    }
    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    /**
     * @param string $real_name
     */
    public function setRealName($real_name)
    {
        $this->real_name = $real_name;
    }
    /**
     * @return string
     */
    public function getRealName()
    {
        return $this->real_name;
    }
    /**
     * @param string $short_name
     */
    public function setShortName($short_name)
    {
        $this->short_name = $short_name;
    }
    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->short_name;
    }
    /**
     * @param string $people_id
     */
    public function setPeopleId($people_id)
    {
        $this->people_id = $people_id;
    }
    /**
     * @return string
     */
    public function getPeopleId()
    {
        return $this->people_id;
    }
    /**
     * @return int
     */
    public function getIsLoggedIn()
    {
        return $this->is_logged_in;
    }
    /**
     * @param int $value
     */
    public function setIsLoggedIn($value)
    {
        $this->is_logged_in = $value;
    }
    /**
     * @param string $login_id
     */
    public function setLoginId($login_id)
    {
        $this->login_id = $login_id;
    }
    /**
     * @return string
     */
    public function getLoginId()
    {
        return $this->login_id;
    }
}
