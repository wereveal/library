<?php
/**
 * Class ModelException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Ritc\Library\Helper\ExceptionHelper;

/**
 * Exceptions specific to database, application rules and business logic operations.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.2
 * @date    2017-12-12 11:32:33
 * @change_log
 * - v1.0.0-alpha.2 - Renumbered codes to be more consistent    - 2017-12-12 wer
 * - v1.0.0-alpha.1 - CustomExceptionAbstract change reflected here.    - 2017-07-15 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-06-11 wer
 */
class ModelException extends CustomExceptionAbstract
{
    /**
     * Returns the text that is associated with the error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return ExceptionHelper::getCodeTextModel($code);
    }
}
