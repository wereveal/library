<?php
/**
 *  @brief An entity class for Users.
 *  @details It needs to be noted that this reflects the fact that
 *      a user entity consists of data that comes from more than one database table.
 *  @file UsersEntity.php
 *  @ingroup library entities
 *  @namespace Ritc/Library/Entities
 *  @class UsersEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-09-11 13:39:34
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 09/11/2014 wer
 *  </pre>
 *  @note <pre>
 *  CREATE TABLE `dbPrefix_users` (
 *    `user_id` int(11) NOT NULL AUTO_INCREMENT,
 *    `user_name` varchar(60) NOT NULL,
 *    `real_name` varchar(50) NOT NULL,
 *    `short_name` varchar(8) DEFAULT NULL,
 *    `password` varchar(255) NOT NULL,
 *    `is_active` tinyint(1) NOT NULL DEFAULT '1',
 *    `is_default` tinyint(1) NOT NULL DEFAULT '0',
 *    `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 *    `bad_login_count` int(11) NOT NULL DEFAULT '0',
 *    `bad_login_ts` int(11) NOT NULL DEFAULT '0'
 *    PRIMARY KEY (`user_id`),
 *    UNIQUE KEY `user_name` (`user_name`)
 *  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 *
 *  INSERT INTO `dbPrefix_users` (`user_id`, `user_name`, `real_name`, `short_name`, `password`, `is_active`, `is_default`, `created_on`, `bad_login_count`, `bad_login_ts`) VALUES
 *  (1, 'SuperAdmin', 'Super Admin', 'GSA', '9715ab56587dd7b748c71644d014250a26b479f28dfdea9927398e3ec1f221ac83da247d016052bb8ee8334320d74c70e1ce48afcc9114d7d837bfc88abb0bc4', 1, 1, '2012-08-11 21:55:28', 0, 0);
**/
namespace Ritc\Library\Entities;

use Ritc\Library\Interfaces\EntityInterface;

class UsersEntity implements EntityInterface
{
    private $user_id = '';
    private $role_id = '';
    private $username = '';
    private $real_name = '';
    private $short_name = '';
    private $password = '';
    private $is_default = 0;
    private $created_on = 0;
    private $bad_login_count = 0;
    private $bad_login_ts = 0;
    private $is_active = 0;
    private $role_level = -1;
    private $role_name = '';
    private $group_id = -1;
    private $group_name = '';

    /**
     * Gets all the entity properties.
     * @return array
     */
    public function getAllProperties()
    {
        return array(
            'user_id'         => $this->user_id,
            'username'        => $this->username,
            'real_name'       => $this->real_name,
            'short_name'      => $this->short_name,
            'password'        => $this->password,
            'is_default'      => $this->is_default,
            'created_on'      => $this->created_on,
            'bad_login_count' => $this->bad_login_count,
            'bad_login_ts'    => $this->bad_login_ts,
            'is_active'       => $this->is_active,
            'role_id'         => $this->role_id,
            'role_name'       => $this->role_name,
            'role_level'      => $this->role_level,
            'group_id'        => $this->group_id,
            'group_name'      => $this->group_name
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
            'user_id'         => -1,
            'username'        => '',
            'real_name'       => '',
            'short_name'      => '',
            'password'        => '',
            'is_default'      => 0,
            'created_on'      => 0,
            'bad_login_count' => 0,
            'bad_login_ts'    => 0,
            'is_active'       => 0,
            'role_id'         => -1,
            'role_name'       => '',
            'role_level'      => -1,
            'group_id'        => -1,
            'group_name'      => ''
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
     * @param int $is_default
     */
    public function setIsDefault($is_default)
    {
        $this->is_default = $is_default;
    }

    /**
     * @return int
     */
    public function getIsDefault()
    {
        return $this->is_default;
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
     * @param string $role_id
     */
    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;
    }

    /**
     * @return string
     */
    public function getRoleId()
    {
        return $this->role_id;
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
     * @param string $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
}
