<?php
/**
 * Class CustomException
 * @package Ritc_Library
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomExceptionAbstract;

/**
 * Class CustomErrorException - generic custom errors.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.0-alpha.0
 * @date    2017-07-15 12:41:34
 * @change_log
 * - v1.0.0-alpha.0 - Initial version        - 2017-07-15 wer
 */
class CustomException extends CustomExceptionAbstract
{
    /**
     * CustomException constructor.
     * @param string     $message
     * @param int        $code
     * @param \Throwable $previous
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        switch ($code) {
            default:
                return parent::getCodeText($code);

        }
    }

}
