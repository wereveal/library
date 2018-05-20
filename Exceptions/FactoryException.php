<?php
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomException;
use Ritc\Library\Helper\ExceptionHelper;
use Throwable;

/**
 * Class FactoryException.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.1
 * @date    2017-12-12 14:57:04
 * ## Change Log
 * - v1.0.0-alpha.1 - implements ExceptionHelper            - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2017-07-16 wer
 */
class FactoryException extends CustomException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getCodeText($code = -1)
    {
        return ExceptionHelper::getCodeTextFactory($code);
    }
}
