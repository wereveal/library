<?php
/**
 * Class TwigThemesModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Services\DbModel;

/**
 * Does database operations on the twig_themes table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2019-03-09 07:02:34
 * @change_log
 * - v1.0.0         - Initial version                           - 2019-03-09 wer
 */
class TwigThemesModel extends ModelAbstract
{
    /**
     * TwigThemesModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'twig_themes');
        $this->setRequiredKeys(['theme_id', 'theme_name']);
    }
}
