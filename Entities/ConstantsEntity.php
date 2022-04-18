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
 * @version 2.0.0
 * @date    2015-10-06 14:20:33
 * @todo add class properties that are missing.
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-26 wer
 * - v1.0.0 - initial version                                   - 2015-10-06 wer
 */
class ConstantsEntity implements EntityInterface
{
    /** @var array  */
    private array $a_properties;

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
    public function setId(int $value):void
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
    public function setName(string $value):void
    {
        $this->a_properties['const_name'] = $value;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->a_properties['const_value'];
    }

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value):void
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
    public function setImmutable(int $value):void
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
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
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
