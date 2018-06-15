<?php
/**
 * Class NavgroupsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the database CRUD stuff for the page table plus other app/business logic.\
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.0
 * @date    2018-06-15 10:59:39
 * @change_log
 * - v1.1.0         - Refactored to extend ModelAbstract            - 2018-06-15 wer
 *                    No functionality changes.
 * - v1.0.0         - initial production version                    - 2017-12-12 wer
 * - v1.0.0-alpha.4 - Refactored to use ModelException              - 2017-06-15 wer
 * - v1.0.0-alpha.3 - Refactoring of DbUtilityTraits reflected here - 2017-01-27 wer
 * - v1.0.0-alpha.2 - Added two methods to get default ng           - 2016-04-18 wer
 * - v1.0.0-alpha.1 - Updated to use DbUtilityTraits                - 2016-03-31 wer
 * - v1.0.0-alpha.0 - Initial version                               - 2016-02-25 wer
 */
class NavgroupsModel extends ModelAbstract
{
    use LogitTraits, DbUtilityTraits;

    /**
     * NavgroupsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'navgroups');
        $this->setRequiredKeys(['ng_name']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Overrides Abstract Methods ###
    /**
     * Generic deletes a record based on the id provided.
     * Checks to see if a map record exists, if so, throws exception;
     *
     * @param int $ng_id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($ng_id = -1)
    {
        if ($ng_id == -1) {
            throw new ModelException('The navgroup id was not provided');
        }
        $o_map = new NavNgMapModel($this->o_db);
        $results = $o_map->readById($ng_id);
        if (!empty($results)) {
            $this->error_message = 'The nav_ng_map record(s) must be deleted first.';
            $err_code = ExceptionHelper::getCodeNumberModel('delete has children');
            throw new ModelException($this->error_message, $err_code);
        }
        try {
            $results = $this->retrieveDefaultId();
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to retrieve the default record.';
            throw new ModelException($this->error_message, $e->getCode(), $e);
        }
        if ($results == $ng_id) {
            $this->error_message = 'This is the default navgroup. Change a different record to be default and try again.';
            $err_code = ExceptionHelper::getCodeNumberModel('delete not permitted');
            throw new ModelException($this->error_message, $err_code);
        }
        try {
            $this->genericDelete($ng_id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * Returns the whole record base on name.
     *
     * @param string $name
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readByName($name = '')
    {
        if ($name == '') {
            throw new ModelException('A record name must be provided.', 220);
        }
        $a_search_values = ['ng_name' => $name];
        try {
            return $this->read($a_search_values, ['order_by' => 'ng_name ASC']);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the navgroup id based on navgroup name.
     *
     * @param string $name
     * @return int
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readIdByName($name = '')
    {
        $a_values = [':ng_name' => $name];
        $a_search_parms = [
            'order_by' => 'ng_id',
            'a_fields' => ['ng_id']
        ];
        try {
            $results = $this->read($a_values, $a_search_parms);
            if (!empty($results[0])) {
                return $results[0]['ng_id'];
            }
            else {
                throw new ModelException('No record found', 230);
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Gets the default navgroup by id.
     * @return int
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function retrieveDefaultId()
    {
        $a_search_for = [':ng_default' => 1];
        $a_search_parms = [
            'order_by' => 'ng_id',
            'a_fields' => ['ng_id']
        ];
        try {
            $results = $this->read($a_search_for, $a_search_parms);
            if (!empty($results[0])) {
                return $results[0]['ng_id'];
            }
            else {
                throw new ModelException('No record found', 230);
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Returns the default navgroup by name.
     * @return string
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function retrieveDefaultName()
    {
        $a_search_for = [':ng_default' => 1];
        $a_search_parms = [
            'order_by' => 'ng_name',
            'a_fields' => ['ng_name']
        ];
        try {
            $results = $this->read($a_search_for, $a_search_parms);
            if (!empty($results[0])) {
                return $results[0]['ng_name'];
            }
            else {
                throw new ModelException('No record found', 230);
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode(), $e);
        }
    }

}
