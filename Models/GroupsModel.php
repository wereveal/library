<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file GroupsModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class GroupsModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 0.1.0
 *  @date 2014-01-18 15:08:49
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v0.1.0 - Initial version 01/18/2014 wer
 *  </pre>
 *  @todo Everything
**/
namespace Ritc\Library\Models;

use Ritc\Library\Core\Arrays;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Core\Elog;
use Ritc\Library\Interfaces\ModelInterface;

class GroupsModel implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_arrays;
    private $o_db;
    private $o_elog;

    public function __construct(DbModel $o_db)
    {
        $this->o_elog    = Elog::start();
        $this->o_db      = $o_db;
        $this->o_arrays  = new Arrays();
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }
    public function create(array $a_values = array())
    {
        return false;
    }
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {
        if (count($a_search_values) > 0) {
            $a_search_params = $a_search_params == array()
                ? array('order_by' => 'group_name')
                : $a_search_params;
            $a_allowed_keys = array(
                'group_id',
                'group_name'
            );
            $a_search_values = $this->o_db->removeBadKeys($a_allowed_keys, $a_search_values);
            $where = $this->o_db->buildSqlWhere($a_search_values, $a_search_params);
        }
        $sql = "
            SELECT group_id, group_name, group_description
            FROM {$this->db_prefix}groups
            {$where}
        ";
        return $this->o_db->search($sql, $a_search_values);
    }
    public function update(array $a_values = array())
    {
        return false;
    }
    public function delete($id = '')
    {
        return false;
    }

    ### Shortcuts ###
    /**
     *  Returns a record of the group specified by id.
     *  @param int $group_id
     *  @return array|bool
     */
    public function readById($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $results = $this->read(array('group_id' => $group_id));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Returns a record of the group specified by name.
     *  @param string $group_name
     *  @return array()
     */
    public function readyByName($group_name = '')
    {
        if ($group_name == '') { return false; }
        $results = $this->read(array('group_name' => $group_name));
        if (count($results[0]) > 0) {
            return $results[0];
        }
        return false;
    }
    /**
     *  Checks to see if the id is a valid group id.
     *  @param int $group_id
     *  @return bool true or false
     **/
    public function isValidGroupId($group_id = -1)
    {
        if ($group_id == -1) { return false; }
        $group_id = (int) $group_id;
        if (is_array($this->read(array('group_id' => $group_id)))) {
            return true;
        }
        return false;
    }
}
