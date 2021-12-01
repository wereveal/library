<?php
/**
 * Class ViewException.
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Traits\LogitTraits;

/**
 * Exceptions class for views.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 1.0.0
 * @date    2018-07-30 13:14:22
 * @change_log
 * - v1.0.0 - Initial version.                               - 2018-07-30 wer
 */
class ViewException extends CustomExceptionAbstract
{
    use LogitTraits;

    /**
     * Returns the text string for the error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return ExceptionHelper::getCodeTextView($code);
    }

    public function getCodeNumberFromText($value = ''):int
    {
        return ExceptionHelper::getCodeNumberView($value);
    }
}
