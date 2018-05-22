<?php
/**
 * Class TwigPrefixModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Elog;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does database operations on the twig_prefix table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.1
 * @date    2018-04-03 17:26:11
 * @change_log
 * - v1.0.1         - bug fixes                     - 2018-04-03 wer
 * - v1.0.0         - Initial production version    - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version               - 2017-05-13 wer
 */
class TwigPrefixModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * TwigPrefixModel constructor.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'twig_prefix');
        $this->o_db = $o_db;
    }

    /**
     * Create a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        $meth            = ' -- ' . __METHOD__;
        $a_required_keys = [
            'twig_prefix',
            'twig_path'
        ];
        $a_psql          = [
            'table_name'  => $this->db_table,
            'column_name' => $this->primary_index_name
        ];
        $a_params        = [
            'a_required_keys' => $a_required_keys,
            'a_field_names'   => $this->a_db_fields,
            'a_psql'          => $a_psql
        ];
        try {
            $a_values = $this->clearDefaultPrefix($a_values);
        }
        catch (ModelException $e) {
            $message = $e->errorMessage() . $meth;
            throw new ModelException($message, $e->getCode());
        }
        try {
            return $this->genericCreate($a_values, $a_params);
        }
        catch (ModelException $exception) {
            $message = $exception->errorMessage() . $meth;
            $code    = $exception->getCode();
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
        $meth         = ' -- ' . __METHOD__;
        $a_parameters = [
            'table_name'     => $this->db_table,
            'a_search_for'   => $a_search_for,
            'a_allowed_keys' => $this->a_db_fields,
            'order_by'       => $this->primary_index_name . ' ASC'
        ];
        $a_parameters = array_merge($a_parameters, $a_search_params);
        $log_message  = 'parameters ' . var_export($a_parameters, true);
        $this->logIt($log_message, LOG_OFF, __METHOD__);
        try {
            return $this->genericRead($a_parameters);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage() . $meth, $e->getCode());
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
        $meth = ' -- ' . __METHOD__;
        try {
            $a_values = $this->clearDefaultPrefix($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException('Update canceled' . $meth, 300, $e);
        }

        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
        }
    }

    /**
     * Deletes a record based on the id provided.
     * @param int $id
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($id = -1)
    {
        $meth = ' -- ' . __METHOD__;
        if ($id == -1) {
            return false;
        }
        $o_tpl = new TwigTemplatesModel($this->o_db);
        if ($this->o_elog instanceof Elog) {
            $o_tpl->setElog($this->o_elog);
        }
        $tpl_pin = $o_tpl->getPrimaryIndexName();
        try {
            $results = $o_tpl->read([$tpl_pin => $id]);
            if (!empty($results)) {
                $this->error_message = "A template exists that uses this prefix" . $meth;
                throw new ModelException($this->error_message, 10);
            }
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
        }
        try {
            $results = $this->genericDelete($id);
            if ($results === false) {
                $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
                throw new ModelException($this->error_message . $meth, 410);
            }
            return $results;
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
        }
    }

    /**
     * Sets the records that are specified as default to not default.
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    private function updateDefaultPrefixOff()
    {
        $meth = ' -- ' . __METHOD__;
        $sql = "UPDATE {$this->db_table} SET tp_default = 'false' WHERE tp_default = 'true'";
        try {
            return $this->o_db->update($sql);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
        }
    }

    /**
     * Checks the values to see if they are trying to set the record to be saved/updated to be the default prefix.
     * @param array $a_values
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    private function clearDefaultPrefix(array $a_values = [])
    {
        $meth       = ' -- ' . __METHOD__;
        $is_default = 'false';
        if (Arrays::isArrayOfAssocArrays($a_values)) {
            foreach ($a_values as $key => $a_record) {
                if (!empty($a_record['tp_default']) && $a_record['tp_default'] == 'true') {
                    if ($is_default === 'false') {
                        $is_default = 'true';
                        try {
                            if (!$this->updateDefaultPrefixOff()) {
                                $this->error_message = "Could not set other prefix as not default.";
                                throw new ModelException($this->error_message . $meth, 110);
                            }
                        }
                        catch (ModelException $e) {
                            $this->error_message = "Could not set other prefix as not default.";
                            throw new ModelException($this->error_message . $meth, 110);
                        }
                    }
                    else {
                        $a_values[$key]['tp_default'] = 'false'; // only one can be default.
                    }
                }
            }

        }
        else {
            if (!empty($a_values['tp_default']) && $a_values['tp_default'] == 'true') {
                try {
                    if (!$this->updateDefaultPrefixOff()) {
                        $this->error_message = "Could not set other prefix as not default.";
                        throw new ModelException($this->error_message . $meth, 110);
                    }
                }
                catch (ModelException $e) {
                    $this->error_message = "Could not set other prefix as not default.";
                    throw new ModelException($this->error_message . $meth, 110);
                }
            }
        }
        return $a_values;
    }
}
