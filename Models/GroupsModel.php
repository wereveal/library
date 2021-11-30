<?php
/**
 * Class GroupsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database CRUD stuff for the groups table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.1.0
 * @date    2021-11-30 13:45:56
 * @change_log
 * - v4.0.0   - Refactored to only do stuff for the groups table                - 2021-11-30 wer
 *              all other business logic moved to GroupsComplexModel
 *              also updated to php8
 * - v3.0.0   - Refactored to use ModelAbstract                                 - 2018-06-15 wer
 * - v2.0.0   - Refactored to use ModelException and DbUtilityTraits            - 2017-06-10 wer
 * - v1.0.0   - First working version                                           - 11/27/2015 wer
 * - v1.0.0β5 - refactoring to provide postgresql compatibility                 - 11/22/2015 wer
 * - v1.0.0β0 - First live version                                              - 09/15/2014 wer
 * - v0.1.0β  - Initial version                                                 - 01/18/2014 wer
 */
class GroupsModel extends ModelAbstract
{
    /**
     * GroupsModel constructor.
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'groups');
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Shortcuts ###
    /**
     * Returns a record of the group specified by name.
     *
     * @param string $group_name
     * @return array
     * @throws ModelException
     */
    public function readByName(string $group_name = ''): array
    {
        if ($group_name === '') {
            throw new ModelException('Missing group name', 220);
        }
        try {
            $results = $this->read(array('group_name' => $group_name));
            if (!empty($results[0])) {
                return $results[0];
            }
            $this->error_message = 'Unable to read the group by ' . $group_name;
            throw new ModelException($this->error_message, 210);
        }
        catch (ModelException) {
            $this->error_message = 'Unable to read the group by ' . $group_name;
            throw new ModelException($this->error_message, 210);
        }

    }

    /**
     * Checks to see if the id is a valid group id.
     *
     * @param int $group_id required
     * @return bool
     */
    public function isValidGroupId(int $group_id = -1):bool
    {
        if (is_numeric($group_id) && $group_id > 0) {
            try {
                $a_results = $this->read(array('group_id' => $group_id));
                if (!empty($a_results)) {
                    return true;
                }
            }
            catch (ModelException) {
                $this->error_message = 'Could not do the read operation.';
            }
        }
        return false;
    }
}
