<?php
/**
 * @brief     Does database operations on the twig_prefix table.
 * @details
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/TwigDirsModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-13 09:14:11
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-13 wer
 * @todo Ritc/Library/Models/TwigDirsModel.php - Everything
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class TwigDirsModel.
 * @class   TwigDirsModel
 * @package Ritc\Library\Models
 */
class TwigDirsModel implements ModelInterface
{
    use LogitTraits, DbUtilityTraits;

    /**
     * TwigDirsModel constructor.
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
            'tp_id',
            'td_name'
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
            throw new ModelException($e->errorMessage(), $e->getCode());
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
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }

    /**
     * Deletes a record based on the id provided.
     * @param int|array $id required, can be single id or array(list) of ids
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function delete($id = -1)
    {
        $o_tpl = new TwigTemplatesModel($this->o_db);
        $o_tpl->setElog($this->o_elog);
        if (Arrays::isArrayOfAssocArrays($id)) {
            foreach ($id as $dir_id) {
                try {
                    $results = $o_tpl->read(['td_id' => $dir_id]);
                    if (!empty($results)) {
                        $this->error_message = "A template exists that uses this directory";
                        throw new ModelException($this->error_message, 436);
                    }
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage(), $e->getCode());
                }
            }
        }
        else {
            try {
                $results = $o_tpl->read(['td_id' => $id]);
                if (!empty($results)) {
                    $this->error_message = "A template exists that uses this directory";
                    throw new ModelException($this->error_message, 436);
                }
            }
            catch (ModelException $e) {
                throw new ModelException($e->errorMessage(), $e->getCode());
            }
        }
        try {
            return $this->genericDelete($id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage(), $e->getCode());
        }
    }
}
