<?php
/**
 * Class UrlsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Handles all the CRUD for the urls table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.0.0
 * @date    2018-06-14 10:58:23
 * @change_log
 * - v2.0.0         - Refactored to extend ModelAbstact     - 2018-06-14 wer
 * - v1.1.1         - ModelException changes reflected here - 2017-12-12 wer
 * - v1.1.0         - should have stayed in beta            - 2017-06-19 wer
 * - v1.0.0         - Out of beta                           - 2017-06-03 wer
 * - v1.0.0-beta.0  - Initial working version               - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2016-04-10 wer
 */
class UrlsModel extends ModelAbstract
{
    /**
     * UrlsModel constructor.
     *
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'urls');
        $this->setRequiredKeys(['url_text']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    ### Overrides Abstract Methods ###
    /**
     * Deletes a record based on the id provided.
     * Overrides method in abstract
     * Checks to see if there are any other tables with relations.
     *
     * @param int|array $id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($id = -1):bool
    {
        if (Arrays::isArrayOfAssocArrays($id)) {
            $a_search_for_route = [];
            foreach ($id as $key => $a_record) {
                if ($this->isValidId($a_record['url_id'])) {
                    if ($this->isImmutable($a_record['url_id'])) {
                        $immut_err_code = ExceptionHelper::getCodeNumberModel('delete immutable');
                        throw new ModelException('Immutable record may not be deleted.', $immut_err_code);
                    }
                    $a_search_for_route[] = ['url_id' => $a_record['url_id']];
                }
                else {
                    $invalid_err_code = ExceptionHelper::getCodeNumberModel('delete missing primary');
                    throw new ModelException('Invalid Primary Index.', $invalid_err_code);
                }
            }
        }
        elseif ($this->isValidId($id)) {
            if ($this->isImmutable($id)) {
                $immut_err_code = ExceptionHelper::getCodeNumberModel('delete immutable');
                throw new ModelException('Immutable record may not be deleted.', $immut_err_code);
            }
            $a_search_for_route = ['url_id' => $id];
        }
        else {
            $invalid_err_code = ExceptionHelper::getCodeNumberModel('delete missing primary');
            throw new ModelException('Invalid Primary Index.', $invalid_err_code);
        }
        $o_routes = new RoutesModel($this->o_db);
        try {
            $search_results = $o_routes->read($a_search_for_route);
            if (isset($search_results[0])) {
                $this->error_message = 'Please change/delete the route that refers to this url first.';
                $child_err_code = ExceptionHelper::getCodeNumberModel('delete has children');
                throw new ModelException($this->error_message, $child_err_code);
            }
        }
        catch (ModelException $e) {
            $message = 'Unable to determine if a route uses this url.';
            $message .= DEVELOPER_MODE
                ? ' -- ' . $e->errorMessage()
                : '';
            $this->error_message = $message;
            $err_code = ExceptionHelper::getCodeNumberModel('delete unknown');
            throw new ModelException($e->getMessage(), $err_code, $e);
        }
        $o_nav = new NavigationModel($this->o_db);
        try {
            $search_results = $o_nav->read($a_search_for_route);
            if (isset($search_results[0])) {
                $this->error_message = 'Please change/delete the Navigation record that refers to this url first.';
                $child_err_code = ExceptionHelper::getCodeNumberModel('delete has children');
                throw new ModelException($this->error_message, $child_err_code);
            }
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $err_code = ExceptionHelper::getCodeNumberModel('delete unknown');
            throw new ModelException($message, $err_code, $e);
        }
        try {
            return $this->genericDelete($id);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            $err_code = ExceptionHelper::getCodeNumberModel('delete unknown');
            throw new ModelException($this->error_message, $err_code, $e);
        }
    }

    ### Specialized Methods ###
    /**
     * Finds Urls that are not assigned to a route.
     *
     * @return mixed
     * @throws ModelException
     */
    public function readNoRoute()
    {
        $sql = "
            SELECT * from {$this->lib_prefix}urls as u
            WHERE NOT EXISTS (
              SELECT * from {$this->lib_prefix}routes as r
              WHERE r.url_id = u.url_id
            )
            AND u.url_text NOT LIKE '%shared%'
        ";
        try {
            return $this->o_db->search($sql);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Read the URLs that are not in the navgroup.
     *
     * @param int $navgroup_id
     * @return mixed
     * @throws ModelException
     */
    public function readNotInNavgroup($navgroup_id = -1)
    {
        $sql = /** @lang text */
        "
            SELECT DISTINCT u.*
            FROM {$this->lib_prefix}urls as u
            JOIN {$this->lib_prefix}navigation as n ON n.url_id = u.url_id
            JOIN {$this->lib_prefix}nav_ng_map as m ON n.nav_id = m.nav_id
            WHERE m.ng_id != $navgroup_id;
        ";
        try {
            return $this->o_db->search($sql);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
