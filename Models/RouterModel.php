<?php
/**
 *  @brief Does all the database CRUD stuff.
 *  @file RouterModel.php
 *  @ingroup library models
 *  @namespace Ritc/Library/Models
 *  @class RouterModel
 *  @author William Reveal  <bill@revealitconsulting.com>
 *  @version 1.0.0β
 *  @date 2014-11-11 10:40:50
 *  @note A file in Ritc Library
 *  @note <pre><b>Change Log</b>
 *      v1.0.0β - First live version - 11/11/2014 wer
 *  </pre>
**/

namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\Base;
use Ritc\Library\Core\Arrays;
use Ritc\Library\Core\DbModel;
use Ritc\Library\Interfaces\ModelInterface;

class RouterModel extends Base implements ModelInterface
{
    private $db_prefix;
    private $db_type;
    private $o_arrays;
    private $o_db;
    protected $o_elog;

    public function __construct(DbModel $o_db)
    {
        $this->o_db      = $o_db;
        $this->o_arrays  = new Arrays;
        $this->db_type   = $this->o_db->getDbType();
        $this->db_prefix = $this->o_db->getDbPrefix();
    }
    /**
     * Generic create a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function create(array $a_values)
    {
        if ($a_values == array()) { return false; }
        $a_required_keys = array(
            'route_path',
            'route_class',
            'route_method',
            'route_action'
        );
        if (!$this->o_arrays->hasRequiredKeys($a_required_keys, $a_values)) {
            return false;
        }
        $sql = "
            INSERT INTO {$this->db_prefix}routes
                (route_path, route_class, route_method, route_action)
            VALUES
                (:route_path, :route_class, :route_method, :route_action)
        ";
        if ($this->o_db->insert($sql, $a_values, "{$this->db_prefix}routes")) {
            $ids = $this->o_db->getNewIds();
            $this->logIt("New Ids: " . var_export($ids , true), LOG_OFF, __METHOD__ . '.' . __LINE__);
            return $ids[0];
        }
        else {
            return false;
        }
    }

    /**
     * Returns an array of records based on the search params provided.
     * @param array $a_search_values
     * @return array
     */
    public function read(array $a_search_values = array(), array $a_search_params = array())
    {

    }

    /**
     * Generic update for a record using the values provided.
     * @param array $a_values
     * @return bool
     */
    public function update(array $a_values)
    {

    }

    /**
     * Generic deletes a record based on the id provided.
     * @param string $id
     * @return bool
     */
    public function delete($id = '')
    {

    }

}
