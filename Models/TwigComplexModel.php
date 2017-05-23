<?php
/**
 * @brief     Does multi-table operations for twig.
 * @details   twig_prefix, twig_dirs, twig_templates are used.
 * @ingroup   lib_models
 * @file      Ritc/Library/Models/TwigComplexModel.php
 * @namespace Ritc\Library\Models
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-05-13 11:52:41
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-05-13 wer
 */
namespace Ritc\Library\Models;

use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Services\Elog;
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
     * Returns complete information regarding a template.
     * @param int $tpl_id
     * @return array|bool
     */
    public function readTplInfo($tpl_id = -1)
    {
        $meth = __METHOD__ . '.';
        if (empty($tpl_id) || !is_numeric($tpl_id)) {
            $this->error_message = 'Must supply a template id';
            return false;
        }
        $tpl_prefix = $this->o_tpls->getLibPrefix();
        $tp_prefix  = $this->o_prefix->getLibPrefix();
        $td_prefix  = $this->o_dirs->getLibPrefix();

        $a_values = [':tpl_id' => $tpl_id];
        $sql =<<<SQL
SELECT t.tpl_id, t.tpl_name, t.tpl_immutable,
  p.tp_id, p.tp_prefix as twig_prefix, p.tp_path as twig_path, p.tp_active, p.tp_default,
  d.td_id, d.td_name as twig_dir
FROM {$tpl_prefix}twig_templates as t
JOIN {$td_prefix}twig_dirs as d
  ON t.td_id = d.td_id
JOIN {$tp_prefix}twig_prefix as p
  ON d.tp_id = p.tp_id
WHERE t.tpl_id = :tpl_id
SQL;
        $this->logIt("TPL info sql: " . $sql, LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->search($sql, $a_values);
        if (empty($results)) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return [];
        }
        return $results;
    }

    /**
     * Returns all active records for the twig configuration.
     * @return array
     */
    public function readTwigConfig()
    {
        $meth = __METHOD__ . '.';
        $tp_prefix = $this->o_tpls->getLibPrefix();
        $td_prefix = $this->o_dirs->getLibPrefix();
        $sql =<<<SQL
SELECT p.tp_id, p.tp_prefix as twig_prefix, p.tp_path as twig_path, p.tp_active, p.tp_default,
  d.td_id, d.td_name as twig_dir
FROM {$tp_prefix}twig_prefix as p
JOIN {$td_prefix}twig_dirs as d
  ON d.tp_id = p.tp_id
WHERE p.tp_active = 1
ORDER BY p.tp_default DESC, p.tp_id ASC
SQL;
        $this->logIt("SQL:\n" . $sql, LOG_OFF, $meth . __LINE__);
        $results = $this->o_db->search($sql);
        $log_message = 'Results: ' . var_export($results, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        if (empty($results)) {
            $this->error_message = $this->o_db->retrieveFormatedSqlErrorMessage();
            return [];
        }
        return $results;
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
        if ($this->o_elog instanceof Elog) {
            $this->o_dirs->setElog($this->o_elog);
            $this->o_prefix->setElog($this->o_elog);
            $this->o_tpls->setElog($this->o_elog);
        }
    }
}