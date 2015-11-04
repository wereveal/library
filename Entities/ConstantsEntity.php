<?php
/**
 *  @brief SQL for constants table and basic accessors.
 *  @file ConstantsEntity.php
 *  @ingroup ritc_library entities
 *  @namespace Ritc\Library\Entities
 *  @class ConstantsEntity
 *  @author William E Reveal
 *  @date 2015-10-06 14:20:33
 *  @version 1.0.0
 *  @note <b>SQL for table<b><pre>
 *      MySQL      - resources/sql/mysql/constants_mysql.sql
 *      PostgreSQL - resources/sql/postgresql/constants_pg.sql</pre>
 */

namespace Ritc\Library\Entities;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\EntityInterface;

class ConstantsEntity implements EntityInterface
{
    private $a_properties;

    public function __construct(array $a_properties = array())
    {
        $this->setAllProperties($a_properties);
    }
    public function getId()
    {
        return $this->a_properties['const_id'];
    }
    public function setId($value)
    {
        $this->a_properties['const_id'] = $value;
    }
    public function getName()
    {
        return $this->a_properties['const_name'];
    }
    public function setName($value)
    {
        $this->a_properties['const_name'] = $value;
    }
    public function getValue()
    {
        return $this->a_properties['const_value'];
    }
    public function setValue($value)
    {
        $this->a_properties['const_value'] = $value;
    }
    public function getImmutable()
    {
        return $this->a_properties['const_immutable'];
    }
    public function setImmutable($value)
    {
        $this->a_properties['const_immutable'] = $value;
    }
    public function getAllProperties()
    {
        return $this->a_properties;
    }
    public function setAllProperties(array $a_properties = array())
    {
        $required_keys = [
            'const_id',
            'const_name',
            'const_value',
            'const_immutable'
        ];
        $a_properties = Arrays::createRequiredPairs($a_properties, $required_keys, 'delete_undesired_keys');
        $this->a_properties = $a_properties;
    }
}
