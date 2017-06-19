<?php
/**
 * @brief     Handles all the CRUD for the urls table.
 * @ingroup   ritc_models
 * @file      UrlsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.1.0
 * @date      2017-06-19 13:08:38
 * @note Change Log
 * - v1.1.0         - should have stayed in beta            - 2017-06-19 wer
 * - v1.0.0         - Out of beta                           - 2017-06-03 wer
 * - v1.0.0-beta.2  - Refactoring from DbUtilityTraits      - 2017-05-09 wer
 * - v1.0.0-beta.1  - Bug fix caused by changes elsewhere   - 2017-01-27 wer
 * - v1.0.0-beta.0  - Initial working version               - 2016-04-13 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2016-04-10 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class UrlsModel.
 * @class   UrlsModel
 * @package Ritc\Library\Models
 */
class UrlsModel implements ModelInterface
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
     * Create a record using the values provided.
     * @param array $a_values
     * @return int
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        $a_required_keys = [
            'url_text'
        ];
        $a_psql = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params = [
            'a_required_keys' => $a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql
        ];
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new ModelException($message, $code);
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_for    key pairs of field name => field value
     * @param array $a_search_params \ref searchparams \ref readparams
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function read(array $a_search_for = [], array $a_search_params = [])
    {
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_for,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => 'url_text ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new ModelException($message, $code);
        }
    }

    /**
     * Update for a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values = [])
    {
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                if ($this->validId($a_record['url_id'])) {
                    if ($this->isImmutable($a_record['url_id'])) {
                        unset($a_values[$key]['url_text']);
                    }
                }
                else {
                    throw new ModelException('Invalid Primary Index.', 310);
                }
            }
        }
        else {
            if ($this->validId($a_values['url_id'])) {
                unset($a_values['url_text']);
            }
            else {
                throw new ModelException('Invalid Primary Index.', 310);
            }
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            $code = $e->getCode();
            throw new ModelException($message, $code);
        }
    }

    /**
     * Deletes a record based on the id provided.
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
                        throw new ModelException('Immutable record may not be deleted.', 440);
                    }
                    $a_search_for_route[] = ['url_id' => $a_record['url_id']];
                }
                else {
                    throw new ModelException('Invalid Primary Index.', 410);
                }
            }
        }
        else {
            if ($this->validId($id)) {
                if ($this->isImmutable($id)) {
                    throw new ModelException('Immutable record may not be deleted.', 440);
                }
                $a_search_for_route = ['url_id' => $id];
            }
            else {
                throw new ModelException('Invalid Primary Index.', 410);
            }
        }
        $o_routes = new RoutesModel($this->o_db);
        try {
            $search_results = $o_routes->read($a_search_for_route);
            if (isset($search_results[0])) {
                $this->error_message = 'Please change/delete the route that refers to this url first.';
                throw new ModelException($this->error_message, 440);
            }
        }
        catch (ModelException $e) {
            $this->error_message = 'Unable to determine if a route uses this url.';
            throw new ModelException($this->error_message, 400);
        }
        $o_nav = new NavigationModel($this->o_db);
        try {
            $search_results = $o_nav->read($a_search_for_route);
            if (isset($search_results[0])) {
                $this->error_message = 'Please change/delete the Navigation record that refers to this url first.';
                throw new ModelException($this->error_message, 440);
            }
        }
        catch (ModelException $e) {
            $message = $e->errorMessage();
            throw new ModelException($message, 400);
        }
        try {
            $results = $this->genericDelete($id);
            return $results;
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            throw new ModelException($this->error_message, 400);
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
            if (!empty($results[0]['url_immutable']) && $results[0]['url_immutable'] === 1) {
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
}
