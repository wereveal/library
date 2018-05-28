<?php
/**
 * Class TwigDirsModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\Arrays;
use Ritc\Library\Interfaces\ModelInterface;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does database operations on the twig_prefix table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.1
 * @date    2018-05-28 12:44:18
 * @change_log
 * - v1.0.1         - fixed default dir names       - 2018-05-28 wer
 * - v1.0.0         - Initial Production version    - 2017-12-15 wer
 * - v1.0.0-alpha.0 - Initial version               - 2017-05-13 wer
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
        $this->setupProperties($o_db, 'twig_dirs');
    }

    /**
     * Create a record using the values provided.
     * @param array $a_values
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function create(array $a_values = [])
    {
        $meth = ' — ' . __METHOD__;
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
            throw new ModelException($e->errorMessage() . $meth, $e->getCode(), $e);
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
        $meth = ' — ' . __METHOD__;
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
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
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
        $meth = ' — ' . __METHOD__;
        try {
            return $this->genericUpdate($a_values);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
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
        $meth = ' — ' . __METHOD__;
        $o_tpl = new TwigTemplatesModel($this->o_db);
        $o_tpl->setElog($this->o_elog);
        if (Arrays::isArrayOfAssocArrays($id)) {
            foreach ($id as $dir_id) {
                try {
                    $results = $o_tpl->read(['td_id' => $dir_id]);
                    if (!empty($results)) {
                        $this->error_message = "A template exists that uses this directory";
                        throw new ModelException($this->error_message . $meth, 436);
                    }
                }
                catch (ModelException $e) {
                    throw new ModelException($e->errorMessage() . $meth, $e->getCode());
                }
            }
        }
        else {
            try {
                $results = $o_tpl->read(['td_id' => $id]);
                if (!empty($results)) {
                    $this->error_message = "A template exists that uses this directory";
                    throw new ModelException($this->error_message . $meth, 436);
                }
            }
            catch (ModelException $e) {
                throw new ModelException($e->errorMessage() . $meth, $e->getCode());
            }
        }
        try {
            return $this->genericDelete($id);
        }
        catch (ModelException $e) {
            throw new ModelException($e->errorMessage() . $meth, $e->getCode());
        }
    }

    /**
     * Creates the records for default directories for the prefix id.
     * @param int $prefix_id Required.
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createDefaultDirs($prefix_id = -1)
    {
        $meth = ' — ' . __METHOD__;
        if ($prefix_id < 1) {
            throw new ModelException('Prefix record id not provided' . $meth, 20);
        }
        if (file_exists(SRC_CONFIG_PATH . '/install_files/default_data.php')) {
            $a_dd = include SRC_CONFIG_PATH . '/install_files/default_data.php';
            $a_default_dirs = $a_dd['twig_default_dirs'];
        }
        else {
            $a_default_dirs = [
                'themes',
                'elements',
                'forms',
                'pages',
                'snippets',
                'tests'
            ];
        }
        $a_dirs = [];
        foreach ($a_default_dirs as $dir) {
            $a_dirs[] = ['tp_id' => $prefix_id, 'td_name' => $dir];

        }
        $a_new_ids = [];
        foreach ($a_dirs as $a_dir) {
            try {
                $a_results = $this->read($a_dir, ['search_type' => 'AND']);
                if (empty($a_results)) {
                    try {
                        $results = $this->create($a_dir);
                        if (empty($results)) {
                            $message = "Unable to create the default dir {$a_dir['td_name']}";
                            throw new ModelException($message . $meth, 110);
                        }
                        $a_new_ids[] = $results[0];
                    }
                    catch (ModelException $e) {
                        $message = "Unable to create the default dirs for {$prefix_id}. ";
                        $message .= DEVELOPER_MODE
                            ? $e->errorMessage()
                            : $e->getMessage();
                        throw new ModelException($message . $meth, $e->getCode(), $e);
                    }
                }
            }
            catch (ModelException $e) {
                $message = "Unable to determine if the default dir exists. ";
                $message .= DEVELOPER_MODE
                    ? $e->errorMessage()
                    : $e->getMessage();
                throw new ModelException($message . $meth, 110);
            }
        }
        return $a_new_ids;
    }
}
