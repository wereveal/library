<?php
/**
 * @brief     Does multi-table operations for twig.
 * @details   twig_prefix, twig_dirs, twig_templates are used.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/TwigComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.1
 * @date      2017-12-15 22:47:39
 * @note Change Log
 * - v1.0.0-alpha.1 - lots of changes        - 2017-12-15 wer
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-13 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Class TwigComplexModel.
 * @class   TwigComplexModel
 * @package Ritc\Library\Models
 */
class TwigComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var \Ritc\Library\Services\Di  */
    private $o_di;
    /** @var  \Ritc\Library\Models\TwigDirsModel */
    private $o_dirs;
    /** @var  \Ritc\Library\Models\TwigPrefixModel */
    private $o_prefix;
    /** @var  \Ritc\Library\Models\TwigTemplatesModel */
    private $o_tpls;

    /**
     * TwigComplexModel constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di = $o_di;
        $this->setupElog($o_di);
        /** @var DbModel $o_db */
        $o_db = $this->o_di->get('db');
        $this->setupProperties($o_db);
        $this->setupDbs($o_db);
    }

    /**
     * @param string $app_twig_prefix
     * @param string $a_app_path
     * @return bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createTwigForApp($app_twig_prefix = 'main_', $a_app_path = '')
    {
        try {
            $results = $this->o_prefix->read(['tp_prefix' => $app_twig_prefix]);
        }
        catch (ModelException $e) {
            $message = 'Unable to determine if the prefix exists. ' . $e->getMessage();
            $message .= DEVELOPER_MODE
                ? ' ' . $e->errorMessage()
                : '';
            throw new ModelException($message, 10);
        }
        if (!empty($results)) {
            $tp_prefix_id = $results[0]['tp_id'];
        }
        else {
            $a_values = [
                'tp_prefix'  => $app_twig_prefix,
                'tp_path'    => $a_app_path,
                'tp_active'  => 1,
                'tp_default' => 0
            ];
            try {
                $results = $this->o_prefix->create($a_values);
                if (empty($results)) {
                    throw new ModelException('Unable to create the twig_prefix record.', 110);
                }
                $tp_prefix_id = $results[0];
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to create the twig_prefix record', $e->getCode());
            }
        }
        try {
            $results = $this->o_dirs->createDefaultDirs($tp_prefix_id);
            if (empty($results)) {
                throw new ModelException('Unable to create the default dir records. No results.', 110);
            }
        }
        catch (ModelException $e) {
            throw new ModelException('Unable to create the default dir records. ' . $e->getMessage(), $e->getCode());
        }
        try {
            $a_search_for = ['tp_id' => $tp_prefix_id, 'td_name' => 'pages'];
            $a_params = ['search_type' => 'AND'];
            $results = $this->o_dirs->read($a_search_for, $a_params);
        }
        catch (ModelException $e) {
            $message = 'Unable to get the pages directory id';
            $message .= DEVELOPER_MODE
                ? ' ' . $e->errorMessage()
                : ' ' . $e->getMessage();
            throw new ModelException($message, 210);
        }
        if (empty($results)) {
            throw new ModelException('Unable to get the pages directory id', 210);
        }
        $dir_id = $results[0]['td_id'];
        $new_templates = [
            ['td_id' => $dir_id, 'tpl_name' => 'index',   'tpl_immutable' => 'false'],
            ['td_id' => $dir_id, 'tpl_name' => 'home',    'tpl_immutable' => 'false'],
            ['td_id' => $dir_id, 'tpl_name' => 'manager', 'tpl_immutable' => 'false'],
            ['td_id' => $dir_id, 'tpl_name' => 'error',   'tpl_immutable' => 'false'],
            ['td_id' => $dir_id, 'tpl_name' => 'text',    'tpl_immutable' => 'false']
        ];
        foreach ($new_templates as $key => $a_template) {
            try {
                $results = $this->o_tpls->read($a_template, ['search_type' => 'AND']);
                if (!empty($results)) {
                    unset($new_templates[$key]);
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Error trying to read the templates.', 210);
            }
        }
        if (!empty($new_templates)) {
            sort($new_templates);
            try {
                $results = $this->o_tpls->create($new_templates);
                if (empty($results)) {
                    throw new ModelException('Could not create the template records. Empty Results.', 110);
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Could not create the template records. ', $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * Returns complete information regarding a template.
     * @param int $tpl_id
     * @return array|bool
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readTplInfo($tpl_id = -1)
    {
        if (empty($tpl_id) || !is_numeric($tpl_id)) {
            $this->error_message = 'Must supply a template id';
            return false;
        }
        $tpl_prefix = $this->o_tpls->getLibPrefix();
        $tp_prefix  = $this->o_prefix->getLibPrefix();
        $td_prefix  = $this->o_dirs->getLibPrefix();

        $a_values = [':tpl_id' => $tpl_id];
        $sql = "
            SELECT t.tpl_id, t.tpl_name, t.tpl_immutable,
              p.tp_id, p.tp_prefix as twig_prefix, p.tp_path as twig_path, p.tp_active, p.tp_default,
              d.td_id, d.td_name as twig_dir
            FROM {$tpl_prefix}twig_templates as t
            JOIN {$td_prefix}twig_dirs as d
              ON t.td_id = d.td_id
            JOIN {$tp_prefix}twig_prefix as p
              ON d.tp_id = p.tp_id
            WHERE t.tpl_id = :tpl_id
        ";
        try {
            return $this->o_db->search($sql, $a_values);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            throw new ModelException($this->error_message, $e->getCode());
        }
    }

    /**
     * Returns all active records for the twig configuration.
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readTwigConfig()
    {
        $tp_prefix = $this->o_tpls->getLibPrefix();
        $td_prefix = $this->o_dirs->getLibPrefix();
        $sql = "
            SELECT p.tp_id, p.tp_prefix as twig_prefix, p.tp_path as twig_path, p.tp_active, p.tp_default,
              d.td_id, d.td_name as twig_dir
            FROM {$tp_prefix}twig_prefix as p
            JOIN {$td_prefix}twig_dirs as d
              ON d.tp_id = p.tp_id
            WHERE p.tp_active = 1
            ORDER BY p.tp_default DESC, p.tp_id ASC
        ";
        try {
            return $this->o_db->search($sql);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            throw new ModelException($this->error_message, $e->getCode());
        }

    }

    /**
     * Creates the required properties containing instances of the respective database models.
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    private function setupDbs(DbModel $o_db)
    {
        $this->o_dirs   = new TwigDirsModel($o_db);
        $this->o_prefix = new TwigPrefixModel($o_db);
        $this->o_tpls   = new TwigTemplatesModel($o_db);
        $this->o_dirs->setElog($this->o_elog);
        $this->o_prefix->setElog($this->o_elog);
        $this->o_tpls->setElog($this->o_elog);
    }
}