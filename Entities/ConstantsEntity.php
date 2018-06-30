<?php
/**
 * Class ConstantsEntity
 * @package Ritc_Library
 */
namespace Ritc\Library\Entities;

use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\EntityInterface;

/**
 * Class ConstantsEntity - Basic accessors for a constants entity.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2015-10-06 14:20:33
 * @todo add class properties that are missing.
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
    public function getId():int
    {
        return $this->a_properties['const_id'];
    }

    /**
     * @param int $value
     */
    public function setId($value):void
    {
        $this->a_properties['const_id'] = $value;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return $this->a_properties['const_name'];
    }

    /**
     * @param string $value
     */
    public function setName($value):void
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
    public function setValue($value):void
    {
        $this->a_properties['const_value'] = $value;
    }

    /**
     * @return int
     */
    public function getImmutable():int
    {
        return $this->a_properties['const_immutable'];
    }

    /**
     * @param int $value
     */
    public function setImmutable($value):void
    {
        $this->a_properties['const_immutable'] = $value;
    }

    /**
     * @return array
     */
    public function getAllProperties():array
    {
        return $this->a_properties;
    }

    /**
     * @param array $a_properties
     * @return bool
     */
    public function setAllProperties(array $a_properties = array()):bool
    {
        $required_keys = [
            'const_id',
            'const_name',
            'const_value',
            'const_immutable'
        ];
        $a_properties = Arrays::createRequiredPairs($a_properties, $required_keys, 'delete_undesired_keys');
        $this->a_properties = $a_properties;
        return true;
    }
}
