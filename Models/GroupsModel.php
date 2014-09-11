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
    public function read(array $a_search_values = array())
    {
        return array();
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
