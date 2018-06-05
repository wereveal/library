<?php
/**
 * Class BlocksModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

/**
 * Basic model class based on the blocks table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-06-02 14:29:32
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-06-02 wer
 */
class BlocksModel extends ModelAbstract
{
    use LogitTraits;

    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'blocks');
    }
}
