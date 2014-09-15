<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file RolesModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class RolesModel
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

class RolesModel implements ModelInterface
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
        return array();
        $sql = "
            SELECT role_id, role_name, role_description, access_level
            FROM {$this->db_prefix}roles
            {$where}
            ORDER BY access_level ASC";
    }
    public function update(array $a_values = array())
    {
        return false;
    }
    public function delete($id = '')
    {
        return false;
    }
    /**
     *  Checks to see if the id is a valid role id.
     *  @param int $role_id
     *  @return bool true or false
     **/
    public function isValidRoleId($role_id = -1)
    {
        if ($role_id == -1) { return false; }
        $role_id = (int) $role_id;
        if (is_array($this->read(array('role_id' => $role_id)))) {
            return true;
        }
        return false;
    }
}
