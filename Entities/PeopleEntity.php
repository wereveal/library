<?php
/**
 *  @brief An entity class for People.
 *  @details It needs to be noted that this reflects the fact that
 *      a user entity consists of data that comes from more than one database table.
 *  @file PeopleEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc/Library/Entities
 *  @class PeopleEntity
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0
 *  @date 2015-07-29 11:41:54
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0 - finalized       - 07/29/2015 wer
 *      v0.1.0 - Initial version - 09/11/2014 wer
 *  </pre>
 *  @note <pre>
 *
 *  MySQL sql
    CREATE TABLE `dbPrefix_people` (
      `people_id` int(11) NOT NULL,
      `login_id` varchar(60) NOT NULL,
      `real_name` varchar(50) NOT NULL,
      `short_name` varchar(8) DEFAULT NULL,
      `password` varchar(128) NOT NULL,
      `is_logged_in` tinyint(2) NOT NULL DEFAULT '0',
      `is_default` tinyint(1) NOT NULL DEFAULT '0',
      `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `bad_login_count` int(11) NOT NULL DEFAULT '0',
      `bad_login_ts` int(11) NOT NULL DEFAULT '0',
      `is_active` tinyint(4) NOT NULL DEFAULT '1'
    ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

    ALTER TABLE `dbPrefix_people`
     ADD PRIMARY KEY (`people_id`), ADD UNIQUE KEY `loginid` (`login_id`);

    ALTER TABLE `dbPrefix_people`
    MODIFY `people_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

    INSERT INTO `dbPrefix_people` (`people_id`, `login_id`, `real_name`, `short_name`, `password`, `is_default`, `created_on`, `bad_login_count`, `bad_login_ts`, `is_active`) VALUES
    (1, 'SuperAdmin', 'Super Admin', 'GSA', '$2y$10$Fj3/Wt2m8WB6qXFHHpCc2u6Nz4o5pxzNE8pZLlWcYQOEqR0yUE6Fi', 1, '2012-08-12 02:55:28', 0, 0, 1);
 *
 *  PostgreSQL
    CREATE SEQUENCE people_id_seq;
    CREATE TABLE {dbPrefix}people (
        people_id integer DEFAULT nextval('people_id_seq'::regclass) NOT NULL,
        login_id character varying(120) NOT NULL,
        real_name character varying(100) NOT NULL,
        short_name character varying(16) DEFAULT NULL::character varying,
        password character varying(510) NOT NULL,
        is_active boolean NOT NULL,
        is_default boolean NOT NULL,
        is_logged_in boolean DEFAULT 0 NOT NULL, 
        created_on timestamp without time zone DEFAULT now() NOT NULL,
        bad_login_count integer DEFAULT 0 NOT NULL,
        bad_login_ts integer DEFAULT 0 NOT NULL
    );
    ALTER TABLE ONLY {dbPrefix}people
        ADD CONSTRAINT {dbPrefix}people_pkey PRIMARY KEY (people_id);
    ALTER TABLE ONLY {dbPrefix}people
        ADD CONSTRAINT {dbPrefix}people_login_id_key UNIQUE (login_id);

    INSERT INTO dbPrefix_people (people_id, login_id, real_name, short_name, password, is_active, is_default, created_on, bad_login_count, bad_login_ts) VALUES
    (1, 'SuperAdmin', 'Super Admin', 'GSA', '9715ab56587dd7b748c71644d014250a26b479f28dfdea9927398e3ec1f221ac83da247d016052bb8ee8334320d74c70e1ce48afcc9114d7d837bfc88abb0bc4', 1, 1, '2012-08-11 21:55:28', 0, 0);
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
    private $is_default = 0;
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
            'is_default'      => $this->is_default,
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
            'is_default'      => 0,
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