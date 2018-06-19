<?php
/**
 * Class ModelAbstract
 * @package Ritc_Library
 */
namespace Ritc\Library\Abstracts;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Abstract which gives a basic setup for a model class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2018-06-06 12:35:38
 * @change_log
 * - v1.1.0         - Changed delete to verify the record is not immutable      - 2018-06-15 wer
 * - v1.0.0         - Initial Production version                                - 2018-06-06 wer
 * - v1.0.0-alpha.0 - Initial version                                           - 2017-07-15 wer
 */
abstract class ModelAbstract implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * Create a record using the values provided.
     *
     * @param array $a_values required
     * @return array The ids of new records.
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        $a_psql = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params = [
            'a_required_keys' => $this->a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql
        ];
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     *
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
            'order_by'       => $this->primary_index_name . ' ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Update for a record using the values provided.
     *
     * @param array  $a_values        required
     * @param array  $a_do_not_change optional, list of field names which should be immutable.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function update(array $a_values = [], array $a_do_not_change = [])
    {
        if (!empty($this->immutable_field) && !empty($a_do_not_change)) {
            $results = $this->fixUpdateValues($a_values, $this->immutable_field , $a_do_not_change);
            if ($results !== false) {
                $a_values = $results;
            }
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Deletes a record based on the id provided.
     * Verifies record is not immutable.
     *
     * @param int $id required to be > 0
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($id = -1)
    {
        if (!empty($this->immutable_field)) {
            try {
                $results = $this->readById($id);
                if ($results[$this->immutable_field] == 'true') {
                    $msg = 'The record is immutable.';
                    $err_code = ExceptionHelper::getCodeNumberModel('delete immutable');
                    throw new ModelException($msg, $err_code);
                }
            }
            catch (ModelException $e) {
                $msg = 'Attempt to verify record is not immutable failed. ' .
                       $e->getMessage();
                throw new ModelException($msg, $e->getCode(), $e);
            }
        }
        try {
            return $this->genericDelete($id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
