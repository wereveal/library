<?php
/**
 * @brief     Does database operations on the twig_prefix table.
 * @details
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/TwigPrefixModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-13 09:14:11
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-13 wer
 * @todo Ritc/Library/Models/TwigPrefixModel.php - Everything
 */
namespace Ritc\Library\Models;

use Ritc\Library\Basic\DbException;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Elog;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class TwigPrefixModel.
 * @class   TwigPrefixModel
 * @package Ritc\Library\Models
 */
class TwigPrefixModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'twig_prefix');
    }

    /**
     * Create a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Basic\DbException
     */
    public function create(array $a_values = [])
    {
        $a_required_keys = [
            'twig_prefix',
            'twig_path'
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
        if (!empty($a_values['tp_default']) && $a_values['tp_default'] == 1) {
            if (!$this->clearDefaultPrefix()) {
                $this->error_message = "Could not set other prefix as not default.";
                throw new DbException($this->error_message, 100);
            }
        }
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (DbException $exception) {
            $message = $exception->errorMessage();
            $code = $exception->getCode();
            throw new DbException($message, $code);
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_for    key pairs of field name => field value
     * @param array $a_search_params \ref searchparams \ref readparams
     * @return array
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
        return $this->genericRead($a_parameters);
    }

    /**
     * Update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values = [])
    {
        if (!isset($a_values[$this->primary_index_name])
            || $a_values[$this->primary_index_name] == ''
            || (!is_numeric($a_values[$this->primary_index_name]))
        ) {
            return false;
        }
        return $this->genericUpdate($a_values);
    }

    /**
     * Deletes a record based on the id provided.
     * @param int $id
     * @return bool
     */
    public function delete($id = -1)
    {
        if ($id == -1) { return false; }
        $o_tpl = new TwigTemplatesModel($this->o_db);
        if ($this->o_elog instanceof Elog) {
            $o_tpl->setElog($this->o_elog);
        }
        $results = $o_tpl->read(['td_id' => $id]);
        if (!empty($results)) {
            $this->error_message = "A template exists that uses this prefix";
            return false;
        }
        $results = $this->genericDelete($id);
        if ($results === false) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
        }
        return $results;
    }

    /**
     * Sets the records that are specified as default to not default.
     * @return bool
     */
    private function clearDefaultPrefix()
    {
        $sql = "
            UPDATE {$this->db_table}
            SET tp_default = 0 
            WHERE tp_default = 1
        ";
        return $this->o_db->update($sql);
    }
}