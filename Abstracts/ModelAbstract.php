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
 * @version v2.1.0
 * @date    2021-11-26 13:55:54
 * @change_log
 * - v2.1.0         - changed property a_do_not_change to a_immutable           - 2021-11-29 wer
 *                    to match most children which extends this abstract
 *                    per compatibility with php8
 * - v2.0.0         - compatibility with php8                                   - 2021-11-26 wer
 * - v1.2.0         - Added allow_pin to create as required by interface        - 2018-10-24 wer
 * - v1.1.0         - Changed delete to verify the record is not immutable      - 2018-06-15 wer
 * - v1.0.0         - Initial Production version                                - 2018-06-06 wer
 * - v1.0.0-alpha.0 - Initial version                                           - 2017-07-15 wer
 */
abstract class ModelAbstract implements ModelInterface
{
    use LogitTraits;
    use DbUtilityTraits;

    /**
     * Create a record using the values provided.
     *
     * @param array $a_values  Required
     * @param bool  $allow_pin Optional, defaults to false. Ordinarily
     *                         the primary index is set automagically but in
     *                         some cases may be manually set by setting this
     *                         to true and specifying the value. Otherwise,
     *                         strips out any values attempted to be set.
     * @return array The ids of new records.
     * @throws ModelException
     */
    public function create(array $a_values = [], bool $allow_pin = false):array
    {
        $a_psql = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params = [
            'a_required_keys' => $this->a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql,
            'allow_pin'       => $allow_pin
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
     * @throws ModelException
     */
    public function read(array $a_search_for = [], array $a_search_params = []):array
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
     * @param array  $a_values    required
     * @param array  $a_immutable optional, list of field names which should be immutable.
     * @return bool
     * @throws ModelException
     */
    public function update(array $a_values = [], array $a_immutable = []):bool
    {
        if (!empty($this->immutable_field) && !empty($a_immutable)) {
            $results = $this->fixUpdateValues($a_values, $this->immutable_field , $a_immutable);
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
     * @throws ModelException
     */
    public function delete(int $id = -1):bool
    {
        if (!empty($this->immutable_field)) {
            try {
                $results = $this->readById($id);
                if ($results[$this->immutable_field] === 'true') {
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
