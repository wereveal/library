<?php
/**
 * Class TwigComplexModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\AuthHelper;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Helper\Strings;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\DbUtilityTraits;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does multi-table operations for twig.
 * twig_prefix, twig_dirs, twig_templates are used.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.1
 * @date    2017-12-15 22:47:39
 * @change_log
 * - v1.0.0         - Initial Production version    - 2018-05-29 wer
 * - v1.0.0-alpha.1 - lots of changes               - 2017-12-15 wer
 * - v1.0.0-alpha.0 - Initial version               - 2017-05-13 wer
 */
class TwigComplexModel
{
    use LogitTraits, DbUtilityTraits;

    /** @var array $a_ids The ids of the new records */
    private $a_ids;
    /** @var \Ritc\Library\Services\Di  */
    private $o_di;
    /** @var  \Ritc\Library\Models\TwigDirsModel */
    private $o_dirs;
    /** @var \Ritc\Library\Models\PageModel */
    private $o_page;
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
        /** @var DbModel $o_db */
        $o_db = $this->o_di->get('db');
        $this->setupProperties($o_db);
        $this->setupDbs($o_db);
    }

    /**
     * Determines if the database record can be deleted based on its children.
     * If it has children, then don't delete.
     *
     * @param string $which_one
     * @param int    $id
     * @return bool
     */
    public function canBeDeleted($which_one = '', $id = -1):?bool
    {
        $meth = __METHOD__ . '.';
        if (empty($which_one) || $id < 1) {
            return false;
        }
        switch ($which_one) {
            case 'tp':
            case 'prefix':
                try {
                    $a_results = $this->o_dirs->read(['tp_id' => $id]);
                    if (empty($a_results)) {
                        return true;
                    }
                    return false;
                }
                catch (ModelException $e) {
                    return false;
                }
            case 'td':
            case 'dir':
            case 'directory':
                try {
                    $a_results = $this->o_tpls->read(['td_id' => $id]);
                    if (empty($a_results)) {
                        return true;
                    }
                    return false;
                }
                catch (ModelException $e) {
                    return false;
                }
                break;
            case 'tpl':
            case 'template':
                try {
                    $a_results = $this->o_page->read(['tpl_id' => $id]);
                    $log_message = 'template reslts ' . var_export($a_results, TRUE);
                    $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

                    if (empty($a_results)) {
                        if ($this->o_tpls->isImmutable([$id])) {
                            $o_auth = new AuthHelper($this->o_di);
                            $login_id = $_SESSION['login_id'];
                            if (!$o_auth->hasMinimumAuthLevel($login_id, 9)) {
                                return false;
                            }
                        }
                        return true;
                    }
                    return false;
                }
                catch (ModelException $e) {
                    return false;
                }
                break;
            default:
                return false;
        }
    }

    /**
     * Creates default records for the app in the three twig tables.
     * @param  array $a_values Required ['tp_prefix', 'tp_path', 'tp_active', 'tp_default']
     * @return array                    ['tp_id', 'td_ids', 'tpl_ids']
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function createTwigForApp(array $a_values = []):array
    {
        if (empty($a_values) || empty($a_values['tp_prefix']) || empty($a_values['tp_path'])) {
            $message = 'Missing required values.';
            throw new ModelException($message, 10);
        }
        $app_twig_prefix = $a_values['tp_prefix'];
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
            if (empty($a_values['tp_active'])) {
                $a_values['tp_active'] = 'true';
            }
            if (empty($a_values['tp_default'])) {
                $a_values['tp_default'] = 'false';
            }
            if (empty($a_values['tp_default'])) {
                $a_values['tp_default'] = 'false';
            }
            try {
                $this->o_prefix->clearDefaultPrefix($a_values);
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to clear the default prefix.');
            }
            try {
                $results = $this->o_prefix->create($a_values);
                if (empty($results)) {
                    $err_code = ExceptionHelper::getCodeNumberModel('create unknown');
                    throw new ModelException('Unable to create the twig_prefix record.', $err_code);
                }
                $tp_prefix_id = $results[0];
            }
            catch (ModelException $e) {
                throw new ModelException('Unable to try to create the twig_prefix record', $e->getCode(), $e);
            }
        }
        try {
            $a_dir_ids = $this->o_dirs->createDefaultDirs($tp_prefix_id);
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
        $a_existing_tpls = [];
        foreach ($new_templates as $key => $a_template) {
            try {
                $results = $this->o_tpls->read($a_template, ['search_type' => 'AND']);
                if (!empty($results)) {
                    $a_existing_tpls[] = $results[0];
                    unset($new_templates[$key]);
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Error trying to read the templates.', 210);
            }
        }
        $a_tpl_ids = [];
        if (!empty($new_templates)) {
            sort($new_templates);
            try {
                $a_tpl_ids = $this->o_tpls->create($new_templates);
                if (empty($a_tpl_ids)) {
                    throw new ModelException('Could not create the template records. Empty Results.', 110);
                }
            }
            catch (ModelException $e) {
                throw new ModelException('Could not create the template records. ', $e->getCode(), $e);
            }
        }
        $a_tpl_ids = array_merge($a_existing_tpls, $a_tpl_ids);
        $a_return_this = [
            'tp_id'   => $tp_prefix_id,
            'td_ids'  => $a_dir_ids,
            'tpl_ids' => $a_tpl_ids
        ];
        return $a_return_this;
    }

    /**
     * Deletes a twig directory record.
     *
     * @param int $td_id
     * @return bool
     * @throws ModelException
     */
    public function deleteDir($td_id = -1):bool
    {
        if ($this->canBeDeleted('dir', $td_id)) {
            try {
                return $this->o_dirs->delete($td_id);
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
        else {
            $this->error_message = 'The record is not allowed to be deleted.';
            $err_no = ExceptionHelper::getCodeNumberModel('delete immutable');
            throw new ModelException($this->error_message, $err_no);
        }
    }

    /**
     * Deletes a twig prefix record.
     *
     * @param int $tp_id
     * @return bool
     * @throws ModelException
     */
    public function deletePrefix($tp_id = -1):bool
    {
        if ($this->canBeDeleted('prefix', $tp_id)) {
            $this->o_prefix->setupElog($this->o_di);
            try {
                return $this->o_prefix->delete($tp_id);
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
        else {
            $this->error_message = 'The prefix has directories still assigned to it.';
            $err_no = ExceptionHelper::getCodeNumberModel('delete has children');
            throw new ModelException($this->error_message, $err_no);
        }
    }

    /**
     * Deletes a twig template record.
     *
     * @param int $tpl_id
     * @return bool
     * @throws ModelException
     */
    public function deleteTpl($tpl_id = -1):bool
    {
        if ($this->canBeDeleted('tpl', $tpl_id)) {
            $this->o_tpls->setupElog($this->o_di);
            try {
                return $this->o_tpls->delete($tpl_id);
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
        else {
            $this->error_message = 'The template is immutable.';
            $err_no = ExceptionHelper::getCodeNumberModel('delete immutable');
            throw new ModelException($this->error_message, $err_no);
        }
    }

    /**
     * Reads the records for the given prefix_id.
     * @param int $prefix_id Required
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readDirsForPrefix($prefix_id = -1):?array
    {
        if ($prefix_id < 1) {
            return [];
        }
        $tp_prefix  = $this->o_prefix->getLibPrefix();
        $td_prefix  = $this->o_dirs->getLibPrefix();
        $sql = /** @lang text */
            "
            SELECT p.tp_id, p.tp_prefix, d.td_id, d.td_name from {$tp_prefix}twig_prefix as p
            JOIN {$td_prefix}twig_dirs as d
              ON p.tp_id = d.tp_id
            WHERE p.tp_active = 'true'
            AND p.tp_id = :tp_id
            ORDER BY d.td_name ASC;
        ";
        $a_values = [':tp_id' => $prefix_id];
        try {
            return $this->o_db->search($sql, $a_values);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, $e->getCode());
        }
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
        $lib_prefix = $this->o_db->getLibPrefix();

        $a_values = [':tpl_id' => $tpl_id];
        $sql = /** @lang text */
            "
            SELECT t.tpl_id, t.tpl_name, t.tpl_immutable,
              p.tp_id, p.tp_prefix as twig_prefix, p.tp_path as twig_path, p.tp_active, p.tp_default,
              d.td_id, d.td_name as twig_dir
            FROM {$lib_prefix}twig_templates as t
            JOIN {$lib_prefix}twig_dirs as d
              ON t.td_id = d.td_id
            JOIN {$lib_prefix}twig_prefix as p
              ON d.tp_id = p.tp_id
            WHERE t.tpl_id = :tpl_id
        ";
        try {
            $a_tpl_info = $this->o_db->search($sql, $a_values);
            if (empty($a_tpl_info)) {
                return [];
            }

            return $a_tpl_info[0];
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, $e->getCode());
        }
    }

    /**
     * Returns all active records for the twig configuration.
     * @param string $is_active
     * @return array
     * @throws \Ritc\Library\Exceptions\ModelException
     */
    public function readTwigConfig($is_active = 'true'):?array
    {
        $tp_prefix = $this->o_tpls->getLibPrefix();
        $td_prefix = $this->o_dirs->getLibPrefix();
        $is_active = $is_active !== 'false'
            ? 'true'
            : 'false';
        $a_values = [':tp_active' => $is_active];
        $order_by = 'p.tp_default DESC, p.tp_id ASC';
        $sql = /** @lang text */
            "
            SELECT p.tp_id, p.tp_prefix as twig_prefix, p.tp_path as twig_path, p.tp_active, p.tp_default,
              d.td_id, d.td_name as twig_dir
            FROM {$tp_prefix}twig_prefix as p
            JOIN {$td_prefix}twig_dirs as d
              ON d.tp_id = p.tp_id
            WHERE p.tp_active = :tp_active
            ORDER BY $order_by
        ";
        try {
            return $this->o_db->search($sql, $a_values);
        }
        catch (ModelException $e) {
            $this->error_message = $this->o_db->retrieveFormattedSqlErrorMessage();
            throw new ModelException($this->error_message, $e->getCode());
        }

    }

    /**
     * Updates or Creates a new twig directory record.
     *
     * @param array  $a_values
     * @param string $action
     * @return bool
     * @throws ModelException
     */
    public function saveDir(array $a_values = [], $action = 'update'):bool
    {
        $meth = __METHOD__ . '.';
        $log_message = 'Values ' . var_export($a_values, TRUE);
        $this->logIt($log_message, LOG_OFF, $meth . __LINE__);

        $td_name = $a_values['td_name'];
        try {
            $results = $this->o_prefix->readById($a_values['tp_id']);
            $new_dir_path = BASE_PATH . $results['tp_path'] . '/' . $td_name;
            if (!file_exists($new_dir_path)) {
                $this->error_message = 'Please create the directory first before entering it here.';
                $err_num = ExceptionHelper::getCodeNumberModel('save missing value');
                throw new ModelException($this->error_message, $err_num);
            }
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            throw new ModelException($e->getMessage(), $e->getCode());
        }
        $a_save_values = [
            'tp_id'   => $a_values['tp_id'],
            'td_name' => $a_values['td_name']
        ];
        if ($action === 'new') {
            try {
                $this->a_ids = $this->o_dirs->create($a_save_values);
                return true;
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->getMessage(), $e->getCode());
            }
        }
        else {
            $a_save_values['td_id'] = $a_values['td_id'];
            try {
                $this->o_dirs->update($a_save_values, ['tp_id']);
                return true;
            }
            catch (ModelException $e) {
                $this->error_message = $e->errorMessage();
                throw new ModelException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * Creates or Updates a twig prefix record.
     *
     * @param array  $a_values
     * @param string $action
     * @return bool
     * @throws ModelException
     */
    public function savePrefix(array $a_values = [], $action = 'update'):bool
    {
        $tp_prefix = $a_values['tp_prefix'];
        $tp_path = $a_values['tp_path'];
        $tp_active = empty($a_values['tp_active'])
            ? 'false'
            : $a_values['tp_active'];
        $tp_default = empty($a_values['tp_default'])
            ? 'false'
            : $a_values['tp_default'];
        if (strrpos($tp_prefix, '_') !== \strlen($tp_prefix) - 1) {
            $tp_prefix .= '_';
        }
        if (strpos($tp_path, '/') !== 0) {
            $tp_path = '/' . $tp_path;
        }
        $a_real_values = [
            'tp_prefix'  => $tp_prefix,
            'tp_path'    => $tp_path,
            'tp_active'  => $tp_active,
            'tp_default' => $tp_default
        ];
        if ($action === 'update_tp') {
            $a_real_values['tp_id'] = $a_values['tp_id'];
        }
        if (!file_exists(BASE_PATH . $tp_path)) {
            $this->error_message = ' File path does not exist. Be sure to create it first, then add the prefix here.';
            $err_no = ExceptionHelper::getCodeNumberModel('missing values');
            throw new ModelException($this->error_message, $err_no);
        }

        try {
            $a_real_values = $this->o_prefix->clearDefaultPrefix($a_real_values);
            if ($action === 'new') {
                $this->a_ids = $this->o_prefix->create($a_real_values);
                return true;
            }

            $this->o_prefix->update($a_real_values);
            return true;
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Creates or updates a new twig template record.
     *
     * @param array  $a_values
     * @param string $action
     * @return bool
     * @throws ModelException
     */
    public function saveTpl(array $a_values = [], $action = 'update'):bool
    {
        $tpl_name  = Strings::makeSnakeCase($a_values['tpl_name']);
        $a_save_values  = [
            'td_id'         => $a_values['td_id'],
            'tpl_name'      => $tpl_name,
            'tpl_immutable' => empty($a_values['tpl_immutable'])
                ? 'false'
                : $a_values['tpl_immutable']
        ];
        try {
            if ($action === 'new') {
                $this->a_ids = $this->o_tpls->create($a_save_values);
                return true;
            }

            $a_save_values['tpl_id'] = $a_values['tpl_id'];
            return $this->o_tpls->update($a_save_values, ['td_id']);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Standard getter for the class property a_ids.
     *
     * @return array
     */
    public function getIds(): array
    {
        return $this->a_ids;
    }

    /**
     * Creates the required properties containing instances of the respective database models.
     *
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    private function setupDbs(DbModel $o_db):void
    {
        $this->a_object_names = ['o_dirs', 'o_prefix', 'o_tpls', 'o_page'];
        $this->o_dirs   = new TwigDirsModel($o_db);
        $this->o_prefix = new TwigPrefixModel($o_db);
        $this->o_tpls   = new TwigTemplatesModel($o_db);
        $this->o_page   = new PageModel($o_db);
        $this->setupElog($this->o_di);
    }
}
