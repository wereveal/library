<?php
/**
 * Class ControllerException
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Ritc\Library\Helper\ExceptionHelper;

/**
 * Class ControllerException - Custom Exceptions for Controllers.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0
 * @date    2018-09-11 09:59:06
 * @change_log
 * - v1.0.0 - Initial version                                   - 2018-09-11 wer
 */
class ControllerException extends CustomExceptionAbstract
{
    /**
     * Returns the exception error code for the string provided.
     *
     * @param string $failure_string
     * @return int
     */
    public function getCodeNumber($failure_string = ''):int
    {
        return ExceptionHelper::getCodeNumberService($failure_string);
    }

    /**
     * Returns the text string for the error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1):string
    {
        return ExceptionHelper::getCodeTextService($code);
    }
}
