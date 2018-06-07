<?php
/**
 * Class UrlsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Handles all the CRUD for the urls table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.1.1
 * @date    2017-12-12 11:35:42
 * @change_log
 * - v1.1.1         - ModelException changes reflected here - 2017-12-12 wer
 * - v1.1.0         - should have stayed in beta            - 2017-06-19 wer
 * - v1.0.0         - Out of beta                           - 2017-06-03 wer
 * - v1.0.0-beta.0  - Initial working version               - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2016-04-10 wer
 */
class UrlsModel extends ModelAbstract
{
    use LogitTraits, DbUtilityTraits;

    /**
     * UrlsModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'urls');
    }

    /**
     * Deletes a record based on the id provided.
     * Overrides method in abstract
     * Checks to see if there are any other tables with relations.
     * @param int|array $id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($id = -1)
    {
        if (Arrays::isArrayOfAssocArrays($id)) {
            $a_search_for_route = [];
            foreach ($id as $key => $a_record) {
                if ($this->validId($a_record['url_id'])) {
                    if ($this->isImmutable($a_record['url_id'])) {
                        throw new ModelException('Immutable record may not be deleted.', 434);
                    }
                    $a_search_for_route[] = ['url_id' => $a_record['url_id']];
                }
                else {
                    throw new ModelException('Invalid Primary Index.', 430);
                }
            }
        }
        else {
            if ($this->validId($id)) {
                if ($this->isImmutable($id)) {
                    throw new ModelException('Immutable record may not be deleted.', 434);
                }
                $a_search_for_route = ['url_id' => $id];
            }
            else {
                throw new ModelException('Invalid Primary Index.', 430);
            }
        }
        $o_routes = new RoutesModel($this->o_db);
        try {
            $search_results = $o_routes->read($a_search_for_route);
            if (isset($search_results[0])) {
                $this->error_message = 'Please change/delete the route that refers to this url first.';
                throw new ModelException($this->error_message, 436);
            }
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to determine if a route uses this url.';
            throw new ModelException($this->error_message, 410);
        }
        $o_nav = new NavigationModel($this->o_db);
        try {
            $search_results = $o_nav->read($a_search_for_route);
            if (isset($search_results[0])) {
                $this->error_message = 'Please change/delete the Navigation record that refers to this url first.';
                throw new ModelException($this->error_message, 436);
            }
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            throw new ModelException($message, 410);
        }
        try {
            $results = $this->genericDelete($id);
            return $results;
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, 410);
        }
    }

    /**
     * Checks to see if the record is immutable.
     * @param string $id
     * @return bool
     */
    public function isImmutable($id = '')
    {
        if (empty($id)) {
            false;
        }
        try {
            $results = $this->read(['url_id' => $id]);
            if (!empty($results[0]['url_immutable']) && $results[0]['url_immutable'] === 'true') {
                return true;
            }
            return false;
        }
        catch (ModelException $e) {
            return true;
        }
    }

    /**
     * Checks to see if the id is valid.
     * @param string $id
     * @return bool
     */
    public function validId($id = '')
    {
        if (empty($id)) {
            false;
        }
        try {
            $results = $this->read(['url_id' => $id]);
            if (!empty($results[0]['url_id']) && $results[0]['url_id'] === $id) {
                return true;
            }
            return false;
        }
        catch (ModelException $e) {
            return false;
        }

    }

    /**
     * Finds Urls that are not assigned to a route.
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
        ";
        try {
            return $this->o_db->search($sql);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
