<?php
/**
 * @brief     Abstract which extends php Exception class.
 * @ingroup   lib_abstracts
 * @file      Ritc/Library/Abstracts/CustomException.php
 * @namespace Ritc\Library\Abstracts
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2018-03-07 08:48:27
 * @note Change Log
 * - v1.0.0         - changed to production, added phpDoc                                               - 2018-03-07 wer
 * - v1.0.0-alpha.1 - expanded errorMessage and getCode                                                 - 2017-07-15 wer
 * - v1.0.0-alpha.0 - Initial version                                                                   - 2016-10-17 wer
 */
namespace Ritc\Library\Abstracts;

use Ritc\Library\Helper\ExceptionHelper;
use Ritc\Library\Interfaces\CustomExceptionInterface;

/**
 * Class CustomException.
 * @class   CustomException
 * @package Ritc\Library\Abstracts
 */
abstract class CustomException extends \Exception implements CustomExceptionInterface
{
    /**
     * Overrides \Exception
     * @return string
     */
    public function __toString()
    {
        return get_class($this) .
        " '{$this->message}' in {$this->file}.{$this->line}\n" .
        $this->getTraceAsString();
    }

    /**
     * Required by Interface.
     * @return string
     */
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

    /**
     * Required by Interface.
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        return ExceptionHelper::getCodeText($code);
    }

    /**
     * Gets the name of the custom exception.
     * @return string
     */
    public function getClass()
    {
        return get_class($this);
    }
}
