<?php
/**
 * Class ContentModel.
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;
use Ritc\Library\Traits\LogitTraits;

/**
 * Does all the database operations and business logic for content.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2018-05-27 18:56:10
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2018-05-27 wer
 * @todo ContentModel.php - Everything
 */
class ContentModel extends ModelAbstract
{
    use LogitTraits;

    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'content');
    }
}
