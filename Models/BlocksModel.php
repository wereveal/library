<?php
/**
 * Class BlocksModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;

/**
 * Basic model class based on the blocks table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 2.0.0
 * @date    2021-11-29 17:16:38
 * @change_log
 * - v2.0.0 - updated for php8 standards                        - 2021-11-29 wer
 * - v1.1.0 - Changed readActive method to work correctly :)    - 2018-12-29 wer
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

    /**
     * Returns only the active blocks.
     * Defaults to solo blocks (blocks that will contain unique data per page).
     * 'shared' and 'all' are other valid options. Invalid value for $b_type
     * returns both.
     *
     * @param string $b_type Optional, defaults to 'solo'.
     * @return array
     * @throws ModelException
     */
    public function readActive(string $b_type = 'solo'):array
    {
        $a_search_for = ['b_active' => 'true'];
        switch ($b_type) {
            case 'shared':
                $a_search_for['b_type'] = 'shared';
                break;
            case 'solo':
                $a_search_for['b_type'] = 'solo';
                break;
            case 'all':
            default:
                // do nothing
        }
        try {
            return $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
