<?php
/**
 * Class ModelException
 * @package RITC_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomException;
use Ritc\Library\Helper\ExceptionHelper;

/**
 * Exceptions specific to database, application rules and business logic operations.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.2
 * @date    2017-12-12 11:32:33
 * ## Change Log
 * - v1.0.0-alpha.2 - Renumbered codes to be more consistent    - 2017-12-12 wer
 * - v1.0.0-alpha.1 - CustomException change reflected here.    - 2017-07-15 wer
 * - v1.0.0-alpha.0 - Initial version                           - 2017-06-11 wer
 */
class ModelException extends CustomException
{
    /**
     * ModelException constructor.
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the text that is associated with the error code.
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        return ExceptionHelper::getCodeTextModel($code);
    }
}
