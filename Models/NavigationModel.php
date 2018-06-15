<?php
/**
 * Class NavigationModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database CRUD stuff for the page table plus other app/business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.0
 * @date    2018-06-15 11:02:54
 * @change_log
 * - v1.1.0         - Refactored to extend ModelAbstract            - 2018-06-15 wer
 * - v1.0.0         - Initial production version                    - 2017-12-12 wer
 * - v1.0.0-alpha.3 - Refactored to use ModelException              - 2017-06-15 wer
 * - v1.0.0-alpha.2 - DbUtilityTraits change reflected here         - 2017-05-09 wer
 * - v1.0.0-alpha.1 - Refactoring in DbUtilityTraits reflected here - 2017-01-27 wer
 * - v1.0.0-alpha.0 - Initial version                               - 02/24/2016 wer
 */
class NavigationModel extends ModelAbstract
{
    /**
     * PageModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'navigation');
        $this->setRequiredKeys(['url_id', 'nav_parent_id', 'nav_name']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    /**
     * Deletes a record based on the id provided.
     * It also deletes the relational records in the nav navgroup map table.
     * @param int $nav_id Required.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function deleteWithMap($nav_id = -1)
    {
        if ($nav_id == -1) {
            $this->error_message = 'Missing required nav id.';
            throw new ModelException($this->error_message, 420);
        }
        /* Going to assume that the map isn't set for relations */
        $o_map = new NavNgMapModel($this->o_db);
        try {
            $this->o_db->startTransaction();
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
        try {
            $o_map->deleteWith(-1, $nav_id); // -1 specifies delete all map records with the nav_id
        }
        catch (ModelException $e) {
            $this->error_message = $o_map->getErrorMessage();
            $this->o_db->rollbackTransaction();
            throw new ModelException($this->error_message, 410);
        }
        try {
            $this->genericDelete($nav_id);
            try {
                $this->o_db->commitTransaction();
            }
            catch (ModelException $e) {
                throw new ModelException($e->errorMessage(), $e->getCode());
            }
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->getSqlErrorMessage();
            try {
                $this->o_db->rollbackTransaction();
                throw new ModelException($this->error_message, $e->getCode());
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->errorMessage(), $e->getCode());
            }
        }
        return true;
    }

    /**
     * Checks to see the the nav has children.
     * @param int $nav_id
     * @return bool
     */
    public function navHasChildren($nav_id = -1)
    {
        if ($nav_id == -1) {
            $this->error_message = 'Missing required nav id';
            return false;
        }
        $sql = "
            SELECT DISTINCT nav_level
            FROM {$this->db_table}
            WHERE nav_parent_id = :nav_parent_id
            AND nav_id != nav_parent_id
        ";
        $a_search_values = [':nav_parent_id' => $nav_id, ];
        try {
            $results = $this->o_db->search($sql, $a_search_values);
            if (count($results) > 0) {
                return true;
            }
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
        }
        return false;
    }
}
