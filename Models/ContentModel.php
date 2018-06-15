<?php
/**
 * Class ContentModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Services\DbModel;

/**
 * Does all the database operations and business logic for content.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-06-15 10:40:35
 * @change_log
 * - v1.0.0 - Initial version                                   - 2018-06-15 wer
 */
class ContentModel extends ModelAbstract
{
    /**
     * ContentModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'content');
        $this->setRequiredKeys(['c_pbm_id']);
    }
}
