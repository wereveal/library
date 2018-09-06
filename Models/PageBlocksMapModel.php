<?php
/**
 * Class PageBlocksMapModel.
 *
 * @package Ritc_Library
 */

namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Services\DbModel;

/**
 * Basic model class based on the page_blocks_map table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-02 14:36:41
 * @change_log
 * - v1.0.0 - Initial version                                       - 2018-06-02 wer
 */
class PageBlocksMapModel extends ModelAbstract
{
    /**
     * PageBlocksMapModel constructor.
     *
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'page_blocks_map');
        $this->setRequiredKeys(['pbm_page_id', 'bm_block_id']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###

    /**
     * Returns all records for a page.
     *
     * @param int $page_id
     * @return array
     * @throws ModelException
     */
    public function readByPageId($page_id = -1)
    {
        $a_search_for = [
            'pbm_page_id' => $page_id
        ];
        try {
            return $this->read($a_search_for);
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}