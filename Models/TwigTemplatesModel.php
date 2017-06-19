<?php
/**
 * @brief     Does database operations on the twig_prefix table.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/TwigTemplatesModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-13 09:14:11
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-13 wer
 * @todo Ritc/Library/Models/TwigTemplatesModel.php - Everything
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class TwigTemplatesModel.
 * @class   TwigTemplatesModel
 * @package Ritc\Library\Models
 */
class TwigTemplatesModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * TwigTemplatesModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'twig_prefix');
    }

    /**
     * Create a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        $a_required_keys = [
            'td_id',
            'tpl_name'
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
            'order_by'       => $this->primary_index_name . ' ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
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
        if ($this->isImmutable($a_values)) {
            if (Arrays::isArrayOfAssocArrays($a_values)) {
                foreach ($a_values as $key => $a_record) {
                    unset($a_values[$key]['td_id']);
                    unset($a_values[$key]['tpl_name']);
                }
            }
            else {
                unset($a_values['td_id']);
                unset($a_values['tpl_name']);
            }
        }
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes a record based on the id provided.
     * @param int|array $id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($id = -1)
    {
        $pim = $this->primary_index_name;
        if (is_array($id)) {
            $ids = [];
            foreach ($id as $the_id) {
               $ids[] = [$pim => $the_id];
            }
        }
        else {
            $ids = [$pim => $id];
        }
        if ($this->isImmutable($ids)) {
            throw new ModelException('The record(s) is immutable', 440);
        }
        try {
            return $this->genericDelete($id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Checks to see if the record is immutable.
     * Obviously only works on existing records.
     * @param $values
     * @return bool
     */
    public function isImmutable($values)
    {
        $pim = $this->primary_index_name;
        if (Arrays::isArrayOfAssocArrays($values)) {
            foreach ($values as $a_record) {
                $id = $a_record[$pim];
                try {
                    $search_results = $this->read([$pim => $id], ['a_fields' => ['tpl_immutable']]);
                    if (isset($search_results[0]) && $search_results[0]['tpl_immutable'] == 1) {
                        return true;
                    }
                }
                catch (ModelException $e) {
                    return true;
                }

            }
        }
        else {
            $id = $values[$pim];
            try {
                $search_results = $this->read([$pim => $id], ['a_fields' => ['tpl_immutable']]);
                if (isset($search_results[0]) && $search_results[0]['tpl_immutable'] == 1) {
                    return true;
                }
            }
            catch (ModelException $e) {
                return true;
            }
        }
        return false;
    }
}