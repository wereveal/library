<?php
/**
 * @brief     Basic accessors for a constants entity.
 * @ingroup   lib_entities
 * @file      Ritc/Library/Entities/ConstantsEntity.php
 * @namespace Ritc\Library\Entities
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2015-10-06 14:20:33
 * @note <b>SQL for table<b><pre>
 *    MySQL      - resources/sql/mysql/constants_mysql.sql
 *    PostgreSQL - resources/sql/postgresql/constants_pg.sql</pre>
 * @todo add class properties that are missing.
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class ConstantsEntity.
 * @class   ConstantsEntity
 * @package Ritc\Library\Entities
 */
class ConstantsEntity implements EntityInterface
{
    /** @var array  */
    private $a_properties;

    /**
     * ConstantsEntity constructor.
     * @param array $a_properties
     */
    public function __construct(array $a_properties = array())
    {
        $this->setAllProperties($a_properties);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->a_properties['const_id'];
    }

    /**
     * @param int $value
     */
    public function setId($value)
    {
        $this->a_properties['const_id'] = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->a_properties['const_name'];
    }

    /**
     * @param string $value
     */
    public function setName($value)
    {
        $this->a_properties['const_name'] = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->a_properties['const_value'];
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->a_properties['const_value'] = $value;
    }

    /**
     * @return int
     */
    public function getImmutable()
    {
        return $this->a_properties['const_immutable'];
    }

    /**
     * @param int $value
     */
    public function setImmutable($value)
    {
        $this->a_properties['const_immutable'] = $value;
    }

    /**
     * @return array
     */
    public function getAllProperties()
    {
        return $this->a_properties;
    }

    /**
     * @param array $a_properties
     * @return bool|void
     */
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
