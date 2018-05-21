<?php
/**
 * Class DbInstallerModel
 * @package RITC_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\Di;
use Ritc\Library\Traits\LogitTraits;

/**
 * Installs default database tables and data.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2017-12-15 14:02:55
 * ## Change Log
 * - v1.0.0         - Initial Production version    - 2017-12-15 wer
 * - v1.0.0-alpha.0 - Initial version               - 2017-11-23 wer
 */
class DbInstallerModel
{
    use LogitTraits;

    /** @var array */
    private $a_data;
    /** @var  array */
    private $a_groups;
    /** @var  array */
    private $a_install_config;
    /** @var  array */
    private $a_navgroups;
    /** @var  array */
    private $a_navigation;
    /** @var  array */
    private $a_nnm;
    /** @var  array */
    private $a_page;
    /** @var  array */
    private $a_people;
    /** @var  array */
    private $a_pgm;
    /** @var  array */
    private $a_rgm;
    /** @var  array */
    private $a_routes;
    /** @var array */
    private $a_sql;
    /** @var  array */
    private $a_twig_dirs;
    /** @var  array */
    private $a_twig_prefix;
    /** @var  array */
    private $a_twig_tpls;
    /** @var  array */
    private $a_urls;
    /** @var string  */
    private $db_prefix;
    /** @var string */
    private $error_message;
    /** @var \Ritc\Library\Services\DbModel */
    private $o_db;
    /** @var \Ritc\Library\Services\Di */
    private $o_di;
    /** @var \PDO */
    private $o_pdo;

    /**
     * DbInstallerModel constructor.
     * @param \Ritc\Library\Services\Di $o_di
     */
    public function __construct(Di $o_di)
    {
        $this->o_di             = $o_di;
        $this->o_db             = $o_di->get('db');
        $this->o_pdo            = $o_di->get('pdo');
        $this->a_sql            = $o_di->getVar('a_sql');
        $this->a_data           = $o_di->getVar('a_data');
        $this->a_install_config = $o_di->getVar('a_install_config');
        $this->db_prefix        = $this->o_db->getLibPrefix();
    }

    /**
     * Creates the default tables.
     * @param array $a_sql
     * @return bool
     */
    public function createTables(array $a_sql = [])
    {
        if (empty($a_sql)) {
            if (empty($this->a_sql)) {
                $this->error_message = 'sql values not provided.';
                return false;
            }
            $a_sql = $this->a_sql;
        }
        foreach ($a_sql as $sql) {
            $sql = str_replace('{dbPrefix}', $this->db_prefix, $sql);
            try {
                $this->o_db->rawExec($sql);
            }
            catch (ModelException $e) {
                $error_message = $this->o_db->getSqlErrorMessage();
                $error_message = "Database failure\n" . var_export($this->o_pdo->errorInfo(), true) . " \nother: " . $error_message . "\n" . $sql . "\n";
                $this->error_message = $error_message;
                return false;
            }
        }
        return true;
    }

    /**
     * Inserts the constants data into the constants table;
     * @param array $a_constants optional as long as it is set
     *                           in the property a_data['constants'].
     * @return bool
     */
    public function insertConstants(array $a_constants = [])
    {
        if (empty($a_constants)) {
            if (empty($this->a_data['constants'])) {
                $this->error_message = 'Constants values not provided.';
                return false;
            }
            $a_constants = $this->a_data['constants'];
        }

        $table_name = $this->db_prefix . 'constants';
        $a_table_info = [
            'table_name'  => $table_name,
            'column_name' => 'const_id'
        ];

        if (!empty($this->a_install_config['twig_prefix']) && isset($a_constants['twig_prefix']['const_value'])) {
            $a_constants['twig_prefix']['const_value'] = $this->a_install_config['twig_prefix'];
        }
        elseif (!empty($this->a_install_config['twig_prefix'])) {
            $a_constants['twig_prefix'] = [
                'const_name'      => 'TWIG_PREFIX',
                'const_value'     => $this->a_install_config['twig_prefix'],
                'const_immutable' => 'true'
            ];
        }
        else if (empty($a_constants['twig_prefix']['const_value'])) {
            $a_constants['twig_prefix'] = [
                'const_name'      => 'TWIG_PREFIX',
                'const_value'     => 'site_',
                'const_immutable' => 'true'
            ];
        }
        if (!empty($this->a_install_config['lib_twig_prefix']) && isset($a_constants['lib_twig_prefix']['const_value'])) {
            $a_constants['lib_twig_prefix']['const_value'] = $this->a_install_config['twig_prefix'];
        }
        elseif (!empty($this->a_install_config['lib_twig_prefix'])) {
            $a_constants['lib_twig_prefix'] = [
                'const_name'      => 'LIB_TWIG_PREFIX',
                'const_value'     => $this->a_install_config['lib_twig_prefix'],
                'const_immutable' => 'true'
            ];
        }
        else if (empty($a_constants['lib_twig_prefix']['const_value'])) {
            $a_constants['lib_twig_prefix'] = [
                'const_name'      => 'LIB_TWIG_PREFIX',
                'const_value'     => 'lib_',
                'const_immutable' => 'true'
            ];
        }

        $results = $this->genericInsert($a_constants, $a_table_info);
        if ($results) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Inserts the groups into the groups data.
     * @param array $a_groups optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertGroups(array $a_groups = [])
    {
        if (empty($a_groups)) {
            if (empty($this->a_data['groups'])) {
                $this->error_message = 'Groups values not provided.';
                return false;
            }
            $a_groups = $this->a_data['groups'];
        }
        $a_table_info = [
            'table_name'  => $this->db_prefix . 'groups',
            'column_name' => 'group_id'
        ];
        $results = $this->genericInsert($a_groups, $a_table_info);
        if ($results) {
            $this->a_groups = $results;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Inserts the data into the navgroups table.
     * @param array $a_navgroups optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertNavgroups(array $a_navgroups = [])
    {
        if (empty($a_navgroups)) {
            if (empty($this->a_data['navgroups'])) {
                $this->error_message = 'NavGroups values not provided.';
                return false;
            }
            $a_navgroups = $this->a_data['navgroups'];
        }
        $a_table_info = [
            'table_name'  => $this->db_prefix . 'navgroups',
            'column_name' => 'ng_id'
        ];
        $results = $this->genericInsert($a_navgroups, $a_table_info);
        if ($results) {
            $this->a_navgroups = $results;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Insert values into the navigation table.
     * @param array $a_navigation optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertNavigation(array $a_navigation = [])
    {
        if (empty($a_navigation)) {
            if (empty($this->a_data['navigation'])) {
                $this->error_message = 'Navigation values not provided.';
                return false;
            }
            $a_navigation = $this->a_data['navigation'];
        }
        $table_name = $this->db_prefix . 'navigation';
        $a_strings = $this->createStrings($a_navigation);

        $nav_sql = "
            INSERT INTO {$table_name}
                ({$a_strings['fields']})
            VALUES
                ({$a_strings['values']})";
        $parent_sql = "
            SELECT nav_id
            FROM {$table_name}
            WHERE nav_name = :nav_name";
        $update_sql = "
            UPDATE {$table_name}
            SET nav_parent_id = :nav_parent_id
            WHERE nav_id = :nav_id";
        $a_nav_table_info = [
            'table_name'  => $table_name,
            'column_name' => 'nav_id'
        ];
        try {
            $o_nav_stmt    = $this->o_db->prepare($nav_sql);
            $o_parent_stmt = $this->o_db->prepare($parent_sql);
            $o_update_stmt = $this->o_db->prepare($update_sql);
        }
        catch (ModelException $e) {
            $this->error_message = "Unable to prepare the sql statement(s)" . $e->errorMessage();
            return false;
        }
        foreach ($a_navigation as $key => $a_record) {
            $a_record['url_id']        = $this->a_urls[$a_record['url_id']]['url_id'];
            $a_record['nav_parent_id'] = 0;
            try {
                $this->o_db->resetNewIds();
                $results = $this->o_db->executeInsert($a_record, $o_nav_stmt, $a_nav_table_info);
                if ($results) {
                    $ids = $this->o_db->getNewIds();
                    $a_navigation[$key]['nav_id'] = $ids[0];
                    $a_navigation[$key]['nav_parent_name'] = $a_navigation[$key]['nav_parent_id'];
                }
                else {
                    $this->error_message = 'Could not insert new navigation record.';
                    return false;
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Could not insert navigation data. ' . $e->errorMessage();
                return false;
            }
        }
        //  Updating nav records with parent ids:
        foreach ($a_navigation as $key => $a_record) {
            $search_values = ['nav_name' => $a_record['nav_parent_name']];
            $update_values = [];
            try {
                $results = $this->o_db->execute($search_values, $o_parent_stmt);
                if ($results === false) {
                    $this->error_message = 'Could not retrieve parent navigation data.';
                }
                $update_values = ['nav_id' => $a_record['nav_id'], 'nav_parent_id' => $results[0]['nav_id']];
            }
            catch (ModelException $e) {
                $this->error_message = 'Could not retrieve parent navigation data. ' . $e->errorMessage();
            }
            try {
                $results = $this->o_db->execute($update_values, $o_update_stmt);
                if ($results === false) {
                    $this->error_message = 'Could not update navigation with parent id.';
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Insert returned empty values' . $e->errorMessage();
            }
        }
        $this->a_navigation = $a_navigation;
        return true;
    }

    /**
     * Inserts the navigation navgroups map data into its table.
     * @param array $a_nnm optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertNNM(array $a_nnm = [])
    {
        print "In insertNNM\n";
        if (empty($a_nnm)) {
            if (empty($this->a_data['nav_ng_map'])) {
                $this->error_message = 'Nav Navgroup Map values not provided.';
                return false;
            }
            $a_nnm = $this->a_data['nav_ng_map'];
        }
        $table_name = $this->db_prefix . 'nav_ng_map';
        $a_strings = $this->createStrings($a_nnm);
        $nnm_sql = "
            INSERT INTO {$table_name}
                ({$a_strings['fields']})
            VALUES
                ({$a_strings['values']})
        ";
        $a_table_info = [
            'table_name'  => $table_name,
            'column_name' => 'nnm_id'
        ];
        try {
            $o_pdo_stmt = $this->o_db->prepare($nnm_sql);
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
              $log_message = 'Couldn’t prepare statement ' . var_export($e->errorMessage(), TRUE);
              print $log_message;
            return false;
        }
        $a_new_nnm = [];
        foreach ($a_nnm as $key => $a_record) {
            $a_new_nnm[] = [
                'ng_id'  => $this->a_navgroups[$a_record['ng_id']]['ng_id'],
                'nav_id' => $this->a_navigation[$a_record['nav_id']]['nav_id']
            ];
        }
        try {
            $results = $this->o_db->executeInsert($a_new_nnm, $o_pdo_stmt, $a_table_info);
            print "New NNM results: " . var_export($results, true) . "\n";
            if ($results) {
                $ids = $this->o_db->getNewIds();
                foreach ($a_new_nnm as $key => $a_record) {
                    $a_new_nnm[$key]['nnm_id'] = $ids[$key];
                }
                print_r($a_new_nnm, true);
            }
            else {
                $this->error_message = 'Could not insert new nav ng map. Empty results';
                return false;
            }
        }
        catch (ModelException $e) {
            $this->error_message = "Could not insert new nav ng map.\n" . $nnm_sql . "\n" . var_export($a_new_nnm, true) . "\n" . $e->errorMessage();
            return false;
        }
        /*
        foreach ($a_nnm as $key => $a_record) {
            $a_new_values = [
                'ng_id'  => $this->a_navgroups[$a_record['ng_id']]['ng_id'],
                'nav_id' => $this->a_navigation[$a_record['nav_id']]['nav_id']
            ];
            $log_message = 'new values:  ' . var_export($a_new_values, TRUE);
            print $log_message;
            $new_a_nnm[] = $a_new_values;
            $this->o_db->resetNewIds();
            try {
                $results = $this->o_db->executeInsert($a_new_values, $o_pdo_stmt, $a_table_info);
                if (empty($results)) {
                    $ids = $this->o_db->getNewIds();
                    $a_nnm[$key]['nnm_id'] = $ids[0];
                }
                else {
                    $this->error_message = 'Could not insert new nav ng map. Empty results';
                    return false;
                }
            }
            catch (ModelException $e) {
                $this->error_message = "Could not insert new nav ng map.\n" . $nnm_sql . "\n" . var_export($new_a_nnm, true) . "\n" . $e->errorMessage();
                return false;
            }
        }
        */
        $this->a_nnm = $a_new_nnm;
        return true;
    }

    /**
     * Inserts the page data.
     * @param array $a_page options, if not provided uses class property a_data['page'].
     * @return bool
     */
    public function insertPage(array $a_page = [])
    {
        if (empty($a_page)) {
            if (empty($this->a_data['page'])) {
                $this->error_message = 'Page data not provided.';
                return false;
            }
            $a_page = $this->a_data['page'];
        }
        $table_name = $this->db_prefix . 'page';
        $a_strings = $this->createStrings($a_page);
        $sql = "
            INSERT INTO {$table_name}
              ({$a_strings['fields']})
            VALUES
              ({$a_strings['values']})
        ";
        try {
            $o_pdo_stmt = $this->o_db->prepare($sql);
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            return false;
        }
        foreach ($a_page as $key => $a_record) {
            $a_record['url_id'] = $this->a_urls[$a_record['url_id']]['url_id'];
            $a_record['tpl_id'] = $this->a_twig_tpls[$a_record['tpl_id']]['tpl_id'];
            try {
                $results = $this->o_db->execute($a_record, $o_pdo_stmt);
                if ($results) {
                    $ids = $this->o_db->getNewIds();
                    $a_page[$key]['page_id'] = $ids[0];
                }
                else {
                    $this->error_message = 'Could not insert a page record.';
                    return false;
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Could not insert a page record.';
                return false;
            }
        }
        $this->a_page = $a_page;
        return true;
    }

    /**
     * Inserts the people data into the people table.
     * @param array $a_people optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertPeople(array $a_people = [])
    {
        if (empty($a_people)) {
            if (empty($this->a_data['people'])) {
                $this->error_message = 'Groups values not provided.';
                return false;
            }
            $a_people = $this->a_data['people'];
        }
        $a_table_info = [
            'table_name'  => $this->db_prefix . 'people',
            'column_name' => 'people_id'
        ];
        $results = $this->genericInsert($a_people, $a_table_info);
        if ($results) {
            $this->a_people = $results;
            return true;
        }
        else {
           return false;
        }
    }

    /**
     * Inserts the values into the people_group_map table.
     * @param array $a_pgm optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertPGM(array $a_pgm = [])
    {
        if (empty($a_pgm)) {
            if (empty($this->a_data['people_group_map'])) {
                $this->error_message = 'people group map values not provided.';
                return false;
            }
            $a_pgm = $this->a_data['people_group_map'];
        }
        $a_table_info = [
            'table_name'  => $this->db_prefix . 'people_group_map',
            'column_name' => 'pgm_id'
        ];
        $a_real_pgm = [];
        foreach ($a_pgm as $key => $a_values) {
            $people_id = $this->a_people[$a_values['people_id']]['people_id'];
            $group_id  = $this->a_groups[$a_values['group_id']]['group_id'];
            $a_real_pgm[$key] = [
                'people_id' => $people_id,
                'group_id'  => $group_id
            ];
        }
        $results = $this->genericInsert($a_real_pgm, $a_table_info);
        if ($results) {
            $this->a_pgm = $results;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Inserts data into the routes_group_map table.
     * @param array $a_rgm optional optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertRGM(array $a_rgm = [])
    {
        if (empty($a_rgm)) {
            if (empty($this->a_data['routes_group_map'])) {
                $this->error_message = 'Routes Group values not provided.';
                return false;
            }
            $a_rgm = $this->a_data['routes_group_map'];
        }
        $a_table_info = [
            'table_name'  => $this->db_prefix . 'routes_group_map',
            'column_name' => 'rgm_id'
        ];
        foreach ($a_rgm as $key => $a_record) {
            $a_rgm[$key]['route_id'] = $this->a_routes[$a_record['route_id']]['route_id'];
            $a_rgm[$key]['group_id'] = $this->a_groups[$a_record['group_id']]['group_id'];
        }
        $results = $this->genericInsert($a_rgm, $a_table_info);
        if ($results) {
            $this->a_rgm = $results;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Inserts the data into the routes table.
     * @param array $a_routes optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertRoutes(array $a_routes = [])
    {
        if (empty($a_routes)) {
            if (empty($this->a_data['routes'])) {
                $this->error_message = 'Route values not provided.';
                return false;
            }
            $a_routes = $this->a_data['routes'];
        }
        $a_table_info = [
            'table_name'  => $this->db_prefix . 'routes',
            'column_name' => 'route_id'
        ];
        foreach ($a_routes as $key => $a_values) {
            $a_routes[$key]['url_id'] = $this->a_urls[$a_values['url_id']]['url_id'];
        }
        $results = $this->genericInsert($a_routes, $a_table_info);
        if ($results) {
            $this->a_routes = $results;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Inserts the twig_dirs data into the table.
     * @param array $a_twig_dirs optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertTwigDirs(array $a_twig_dirs = [])
    {
        if (empty($a_twig_dirs)) {
            if (empty($this->a_data['twig_dirs'])) {
                $this->error_message = 'Data for twig_dirs not provided.';
                return false;
            }
            $a_twig_dirs = $this->a_data['twig_dirs'];
        }
        $table_name = $this->db_prefix . 'twig_dirs';
        $a_strings = $this->createStrings($a_twig_dirs);
        $a_twig_prefix = $this->a_twig_prefix;
        $sql = "
            INSERT INTO {$table_name}
                ({$a_strings['fields']})
            VALUES
                ({$a_strings['values']})
        ";
        $a_table_info = [
            'table_name' => $table_name,
            'column_name' => 'td_id'
        ];
        try {
            $o_pdo_stmt = $this->o_db->prepare($sql);
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            return false;
        }
        foreach ($a_twig_dirs as $key => $a_record) {
            $tp_id                    = $a_twig_prefix[$a_record['tp_id']]['tp_id'];
            $a_twig_dirs[$key]['tp_id'] = $tp_id;
            $a_record['tp_id']        = $tp_id;
            try {
                $this->o_db->resetNewIds();
                $results = $this->o_db->executeInsert($a_record, $o_pdo_stmt, $a_table_info);
                if ($results) {
                    $ids = $this->o_db->getNewIds();
                    $a_twig_dirs[$key]['td_id'] = $ids[0];
                }
                else {
                    $this->error_message = 'Could not insert tp dir record.';
                    return false;
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Could not insert tp dir record.';
                return false;
            }
        }
        $this->a_twig_dirs = $a_twig_dirs;
        return true;
    }

    /**
     * Inserts the twig prefixes data into the table.
     * @param array $a_twig_prefix optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertTwigPrefixes(array $a_twig_prefix = [])
    {
        if (empty($a_twig_prefix)) {
            if (empty($this->a_data['twig_prefix'])) {
                $this->error_message = 'twig_prefix data not provided for the method.';
                return false;
            }
            $a_twig_prefix = $this->a_data['twig_prefix'];
        }
        $a_strings = $this->createStrings($a_twig_prefix);
        $table_name = $this->db_prefix . 'twig_prefix';
        $sql = "
            INSERT INTO {$table_name}
              ({$a_strings['fields']})
            VALUES
              ({$a_strings['values']})
        ";
        $a_table_info = [
            'table_name'  => $table_name,
            'column_name' => 'tp_id'
        ];
        try {
            $o_pdo_stmt = $this->o_db->prepare($sql);
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            return false;
        }
        foreach ($a_twig_prefix as $key => $a_record) {
            try {
                $this->o_db->resetNewIds();
                $results = $this->o_db->executeInsert($a_record, $o_pdo_stmt, $a_table_info);
                if ($results) {
                    $ids = $this->o_db->getNewIds();
                    $a_twig_prefix[$key]['tp_id'] = $ids[0];
                }
            }
            catch (ModelException $e) {
                $this->error_message = "Could not insert new twig prefix record.";
                return false;
            }
        }
        $this->a_twig_prefix = $a_twig_prefix;
        return true;
    }

    /**
     * Inserts data into the twig_templates table.
     * @param array $a_twig_tpls optional, if not given takes values from class property $a_data.
     * @return bool
     */
    public function insertTwigTemplates(array $a_twig_tpls = [])
    {
        if (empty($a_twig_tpls)) {
            if (empty($this->a_data['twig_templates'])) {
                $this->error_message = 'Data missing for the twig_templates';
                return false;
            }
            $a_twig_tpls = $this->a_data['twig_templates'];
        }
        $a_twig_dirs = $this->a_twig_dirs;
        $table_name = $this->db_prefix . 'twig_templates';
        $a_strings = $this->createStrings($a_twig_tpls);
        $sql = "
            INSERT INTO {$table_name}
              ({$a_strings['fields']})
            VALUES
              ({$a_strings['values']})
        ";
        $a_table_info = [
            'table_name'  => $table_name,
            'column_name' => 'tpl_id'
        ];
        try {
            $o_pdo_stmt = $this->o_db->prepare($sql);
        }
        catch (ModelException $e) {
            $this->error_message = $e->errorMessage();
            return false;
        }
        foreach ($a_twig_tpls as $key => $a_record) {
            $td_id                      = $a_twig_dirs[$a_record['td_id']]['td_id'];
            $a_twig_tpls[$key]['td_id'] = $td_id;
            $a_record['td_id']          = $td_id;
            try {
                $this->o_db->resetNewIds();
                $results = $this->o_db->executeInsert($a_record, $o_pdo_stmt, $a_table_info);
                if ($results) {
                    $ids = $this->o_db->getNewIds();
                    $a_twig_tpls[$key]['tpl_id'] = $ids[0];
                }
                else {
                    $this->error_message = 'Could not insert a new twig template record';
                    return false;
                }
            }
            catch (ModelException $e) {
                $this->error_message = 'Could not insert a new twig template record';
                return false;
            }
        }
        $this->a_twig_tpls = $a_twig_tpls;
        return true;
    }

    /**
     * Inserts the URLs into the urls table.
     * @param array $a_urls
     * @return bool
     */
    public function insertUrls(array $a_urls = [])
    {
        if (empty($a_urls)) {
            if (empty($this->a_data['urls'])) {
                $this->error_message = 'URL values not provided.';
                return false;
            }
            $a_urls = $this->a_data['urls'];
        }
        $a_table_info = [
            'table_name'  => $this->db_prefix . 'urls',
            'column_name' => 'url_id'
        ];
        $results = $this->genericInsert($a_urls, $a_table_info);
        if ($results) {
            $this->a_urls = $results;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Generic Insert method used by the other insert methods.
     * @param array $a_values_list
     * @param array $a_table_info
     * @return array|bool
     */
    private function genericInsert(array $a_values_list = [], array $a_table_info = [])
    {
        if (empty($a_values_list) || empty($a_table_info)) {
            $this->error_message = 'Missing required information';
            return false;
        }
        $a_strings = $this->createStrings($a_values_list);
        $sql = "
            INSERT INTO {$a_table_info['table_name']}
              ({$a_strings['fields']})
            VALUES
              ({$a_strings['values']})
        ";
        foreach ($a_values_list as $key => $a_values) {
            try {
                $results = $this->o_db->insert($sql, $a_values, $a_table_info);
                if (empty($results)) {
                    $this->error_message = 'Could not insert constants: insert did not return valid values';
                    return false;
                }
                else {
                    $ids = $this->o_db->getNewIds();
                    $a_values_list[$key][$a_table_info['column_name']] = $ids[0];
                }
            }
            catch (ModelException $e) {
                $this->error_message = "Could not insert constants data. " . $e->errorMessage() . "\n\n" . $this->o_db->retrieveFormattedSqlErrorMessage() . "\n\n" . $sql;
                return false;
            }
        }
        return $a_values_list;

    }

    ### Utility Methods ###
    /**
     * Creates strings needed for the insert sql.
     * @param array $a_records
     * @return array
     */
    private function createStrings($a_records = []) {
        $a_record = array_shift($a_records);
        $fields = '';
        $values = '';
        foreach ($a_record as $key => $a_value) {
            $fields .= $fields == '' ? $key : ', ' . $key;
            $values .= $values == '' ? ':' . $key : ', :' . $key;
        }
        return [
            'fields' => $fields,
            'values' => $values
        ];
    }

    /**
     * Updates the property a_data twig data when the app has a unique prefix.
     */
    public function createTwigAppConfig()
    {
        if (!empty($this->a_install_config['app_twig_prefix'])) {
            $app_twig_prefix = $this->a_install_config['app_twig_prefix'];
            $key_name = str_replace('_', '', $app_twig_prefix);
            if (!isset($this->a_data['twig_prefix'][$key_name])) {
                $tp_path = '/src/apps'
                    . $this->a_install_config['namespace']
                    . '/'
                    . $this->a_install_config['app_name']
                    . '/resources/templates';
                $this->a_data['twig_prefix'][$key_name] = [
                    'tp_prefix'  => $app_twig_prefix,
                    'tp_path'    => $tp_path,
                    'tp_active'  => 'true',
                    'tp_default' => 'false'
                ];
            }
            $a_dir_names = $this->a_data['twig_default_dirs'];
            foreach ($a_dir_names as $name) {
                if (!isset($this->a_data['twig_dirs'][$app_twig_prefix . $name])) {
                    $this->a_data['twig_dirs'][$app_twig_prefix . $name] = [
                        'tp_id'   => $key_name,
                        'td_name' => $name
                    ];
                }
            }
            if (!isset($this->a_data['twig_templates'][$app_twig_prefix . 'index'])) {
                $this->a_data['twig_templates'][$app_twig_prefix . 'index'] = [
                    'td_id'         => $app_twig_prefix . 'pages',
                    'tpl_name'      => 'index',
                    'tpl_immutable' => 'false'
                ];
            }
        }
    }

    ### GET and SET methods ###
    /**
     * Gets the property a_data.
     * @return array
     */
    public function getData()
    {
        return $this->a_data;
    }

    /**
     * Sets the property a_data.
     * @param array $a_data
     */
    public function setData(array $a_data = [])
    {
        $this->a_data = $a_data;
    }

    /**
     * Gets the property error_message.
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * Sets the property error_message.
     * @param string $value
     */
    public function setErrorMessage($value = '')
    {
        $this->error_message = $value;
    }

    /**
     * Gets the property a_install_config.
     * @return array
     */
    public function getInstallConfig()
    {
        return $this->a_install_config;
    }

    /**
     * Sets the property a_install_config.
     * @param array $a_values
     */
    public function setInstallConfig(array $a_values = [])
    {
        $this->a_install_config = $a_values;
    }

    /**
     * Gets the property a_sql.
     * @return array
     */
    public function getSql()
    {
        return $this->a_sql;
    }

    /**
     * Sets the property a_sql.
     * @param array $a_sql
     */
    public function setSql(array $a_sql = [])
    {
        $this->a_sql = $a_sql;
    }

}
