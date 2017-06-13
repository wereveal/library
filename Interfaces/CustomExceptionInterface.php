<?php
/**
 * @brief     Interface for Custom Exception Classes.
 * @ingroup   lib_interfaces
 * @file      Ritc/Library/Interfaces/CustomExceptionInterface.php
 * @namespace Ritc\Library\Interfaces
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2016-10-17 11:20:07
 * @note <b>Change Log</b>
 * - v1.0.0.alpha-0 initial version                                                                     - 2016-10-17 wer
 */
namespace Ritc\Library\Interfaces;

/**
 * Interface CustomExceptionInterface
 * @class CustomExceptionInterface
 * @package Ritc\Library\Interfaces
 */
interface CustomExceptionInterface
{
    /* Protected methods inherited from Exception class */
    public function getMessage();                 // Exception message
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
    public function getTraceAsString();           // Formated string of trace

    /* Overrides methods inherited from Exception class */
    public function __toString();                 // formated string for display

    /* Adds methods to the Exception class */
    public function errorMessage();               // error message with additional information
    public function getCodeText();                // associate text with a code
}
