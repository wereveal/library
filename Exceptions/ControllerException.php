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
 * @version v2.0.0
 * @date    2021-11-26 16:32:59
 * @change_log
 * - v2.0.0 - updated for php8                                  - 2021-11-26 wer
 * - v1.0.0 - Initial version                                   - 2018-09-11 wer
 */
class ControllerException extends CustomExceptionAbstract
{
    /**
     * Returns the exception error code for the string provided.
     *
     * @param string $value
     * @return int
     */
    public function getCodeNumber(string $value = ''):int
    {
        return ExceptionHelper::getCodeNumberService($value);
    }

    /**
     * Returns the text string for the error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return ExceptionHelper::getCodeTextService($code);
    }
}
