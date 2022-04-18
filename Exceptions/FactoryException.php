<?php
/**
 * Class FactoryException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Ritc\Library\Helper\ExceptionHelper;

/**
 * Class FactoryException.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.1
 * @date    2017-12-12 14:57:04
 * @change_log
 * - v1.0.0-alpha.1 - implements ExceptionHelper            - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2017-07-16 wer
 */
class FactoryException extends CustomExceptionAbstract
{
    /**
     * Returns the text string associated with the error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return ExceptionHelper::getCodeTextFactory($code);
    }
}
