<?php
/**
 * Class BlocksModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Services\DbModel;

/**
 * Basic model class based on the blocks table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-02 14:29:32
 * @change_log
 * - v1.0.0 - Initial version                                   - 2018-06-02 wer
 */
class BlocksModel extends ModelAbstract
{
    /**
     * BlocksModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'blocks');
        $this->setRequiredKeys(['b_name']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###
}
