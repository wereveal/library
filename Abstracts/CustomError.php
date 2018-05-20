<?php
namespace Ritc\Library\Abstracts;

/**
 * Abstract which extends php Exception class.
 *
 * @package RITC_Library
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @date    2017-07-15 12:42:53
 * @version v1.0.0-alpha.0
 * ## Change Log
 * - v1.0.0-alpha.0 - Initial version                                                                   - 2017-07-15 wer
 */
abstract class CustomError extends \ErrorException
{
    public function __toString()
    {
        return get_class($this) .
        " '{$this->message}' in {$this->file}.{$this->line}\n" .
        $this->getTraceAsString();
    }

    public function errorMessage()
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

    public function getCodeText($code = -1)
    {
        switch ($code) {
            ### Business Logic Errors ###
            case 600:
                return 'General Business Logic Error.';
            ### Application Rule Errors ###
            case 700:
                return 'General Application Rule Error.';
            ### Generic Errors ###
            case 800:
                return 'Unable to create the instance';
            case 900:
                return 'General Error, see error message';
            case 999:
                return 'Unspecified Error.';
            default:
                return 'Unspecified error';
        }
    }

    public function getClass()
    {
        return get_class($this);
    }
}
