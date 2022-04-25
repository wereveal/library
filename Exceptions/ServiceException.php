<?php
/**
 * Class ServiceException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Ritc\Library\Helper\ExceptionHelper;

/**
 * Class ServiceException - Custom Exceptions for Services.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0-alpha.0
 * @date    2017-07-16 08:18:12
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-16 wer
 */
class ServiceException extends CustomExceptionAbstract
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
