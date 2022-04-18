<?php
/**
 * Class TwigTemplatesModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Services\DbModel;

/**
 * Does database operations on the twig_templates table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.1.0
 * @date    2018-06-15 11:49:14
 * @change_log
 * - v1.1.0         - Refactored to extend ModelAbstract        - 2018-06-15 wer
 *                    No functionality changes
 * - v1.0.0         - Initial production version                - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-05-13 wer
 */
class TwigTemplatesModel extends ModelAbstract
{
    /**
     * TwigTemplatesModel constructor.
     *
     * @param DbModel $o_db
     */
    public function __construct(DbModel $o_db)
    {
        $this->setupProperties($o_db, 'twig_templates');
        $this->setRequiredKeys(['td_id', 'tpl_name', 'theme_id']);
    }
}
