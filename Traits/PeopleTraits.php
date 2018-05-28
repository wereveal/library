<?php
/**
 * Trait PeopleTraits
 * @package Ritc_Library
 */
namespace Ritc\Library\Traits;

/**
 * Functions that could be used in several people related cases.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2016-12-08 12:17:39
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2016-12-08 wer
 */
trait PeopleTraits
{
    /**
     * Returns an array mapping a person to the group(s) specified.
     * @param string $people_id
     * @param array  $a_groups
     * @return array
     */
    public function makePgmArray($people_id = '', array $a_groups = array())
    {
        if ($people_id == '' || $a_groups == array()) {
            return array();
        }
        $a_return_map = array();
        foreach ($a_groups as $group_id) {
            $a_return_map[] = ['people_id' => $people_id, 'group_id' => $group_id];
        }
        return $a_return_map;
    }

}
