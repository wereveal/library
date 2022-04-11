<?php
/**
 * Class TwigThemesModel
 * @package Ritc_Library
 */
namespace Ritc\Library\Models;

use Ritc\Library\Abstracts\ModelAbstract;
use Ritc\Library\Exceptions\ModelException;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Services\DbModel;

/**
 * Does database operations on the twig_themes table.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
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

    /**
     * @param string $name
     * @return int
     * @throws ModelException
     */
    public function readIdByName(string $name): int
    {
        try {
            $results = $this->read(['theme_name' => $name]);
            if (empty($results[0]['theme_id'])) {
                throw new ModelException('Theme name not valid.', ExceptionHelper::getCodeNumberModel('read_no_results'));
            }
            return $results[0]['theme_id'];
        }
        catch (ModelException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
