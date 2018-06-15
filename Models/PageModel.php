<?php
/**
 * Class PageModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database CRUD stuff for the page table plus other app/business logic.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.0.0
 * @date    2018-06-15 09:16:14
 * @change_log
 * - v3.0.0   - Refactored to use ModelAbstract                         - 2018-06-15 wer
 * - v2.0.0   - Refactored to use ModelException                        - 2017-06-17 wer
 * - v1.2.0   - refactored to utilize the DbUtilityTraits               - 2016-04-01 wer
 * - v1.1.0   - refactoring changes to DbModel reflected here.          - 2016-03-19 wer
 * - v1.0.0   - take out of beta                                        - 11/27/2015 wer
 * - v1.0.0β1 - Initial version                                         - 10/30/2015 wer
 */
class PageModel extends ModelAbstract
{
    /**
     * PageModel constructor.
     *
     * @param \Ritc\Library\Services\DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'page');
        $this->setRequiredKeys(['url_id', 'page_title']);
    }

    ### Abstract Methods ###
    # create(array $a_values = [])
    # read(array $a_search_for = [], array $a_search_params = [])
    # update(array $a_values = [], array $a_do_not_change = [])
    # delete($id = -1)
    ###
}
