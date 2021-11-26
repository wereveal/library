<?php
/**
 * Class CustomErrorAbstract
 * @package Ritc_Library
 */
namespace Ritc\Library\Abstracts;

use ErrorException;

/**
 * Abstract which extends php Exception class.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @date    2021-11-26 13:49:17
 * @version v1.0.0
 * @change_log
 * - v1.0.0 - Initial Reworked version for php 8                - 2021-11-26 wer
 */
abstract class CustomErrorAbstract extends ErrorException
{
    /**
     * Overrides the base method.
     * @return string
     */
    public function __toString()
    {
        return get_class($this) .
        " '{$this->message}' in {$this->file}.{$this->line}\n" .
        $this->getTraceAsString();
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function errorMessage():string
    {
        $error_message = $this->getMessage();
        if (empty($error_message)) {
            $error_message = $this->getCodeText($this->getCode());
        }
        $error_message .= ' -- ' . $this->getClass() . '.' . $this->getLine();

        $previous = $this->getPrevious();
        if ($previous) {
            $msg  = $previous->getMessage();
            $code = $previous->getCode();
            $line = $previous->getLine();
            $file = $previous->getFile();
            $error_message .= ' - Previous: ' . $msg . ' -- ' . $file . '.' . $line . '(code: ' . $code . ')';
        }
        return $error_message;
    }

    /**
     * Returns the text of the associated error code.
     *
     * @param int $code
     * @return string
     */
    public function getCodeText(int $code = -1):string
    {
        return match ($code) {
            600 => 'General Business Logic Error.',
            700 => 'General Application Rule Error.',
            800 => 'Unable to create the instance',
            900 => 'General Error, see error message',
            999 => 'Unspecified Error.',
            default => 'Unspecified error',
        };
    }

    /**
     * Returns the class.
     *
     * @return string
     */
    public function getClass():string
    {
        return get_class($this);
    }
}
