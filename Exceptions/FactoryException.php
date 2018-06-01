<?php
/**
 * Class FactoryException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;
use Ritc\Library\Helper\ExceptionHelper;
use Throwable;

/**
 * Class FactoryException.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.1
 * @date    2017-12-12 14:57:04
 * @change_log
 * - v1.0.0-alpha.1 - implements ExceptionHelper            - 2017-12-12 wer
 * - v1.0.0-alpha.0 - Initial version                       - 2017-07-16 wer
 */
class FactoryException extends CustomExceptionAbstract
{
    /**
     * FactoryException constructor.
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the text string associated with the error code.
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        return ExceptionHelper::getCodeTextFactory($code);
    }
}
