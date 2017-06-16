<?php
/**
 * @brief     Exceptions specific to database, application rules and business logic operations.
 * @ingroup   lib_exceptions
 * @file      Ritc/Library/Exceptions/ModelException.php
 * @namespace Ritc\Library\Exceptions
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0-alpha.0
 * @date      2017-06-11 14:24:36
 * @note Change Log
 * - v1.0.0-alpha.0 - Initial version        - 2017-06-11 wer
 */
namespace Ritc\Library\Exceptions;

use Ritc\Library\Abstracts\CustomException;

/**
 * Class ModelException.
 * @class   ModelException
 * @package Ritc\Library\Basic
 */
class ModelException extends CustomException
{
    /**
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
     * @param int $code
     * @return string
     */
    public function getCodeText($code = -1)
    {
        switch ($code) {
            # Generic Database failures
            case 10:
                return 'Unable to do the database operation';
            case 20:
                return 'Unable to connect to the database';
            case 30:
                return 'Unable to start a transaction.';
            case 40:
                return 'Unable to commit a transaction.';
            case 45:
                return 'Unable to rollback a transaction.';
            case 50:
                return 'Unable to prepare the statement.';
            case 60:
                return 'Unable to do a PDO operation.';
            case 65:
                return 'Unable to do a PDOStatement operation.';
            case 70:
                return 'Missing required values';
            case 80:
                return 'Invalid values.';
            # Create Codes
            case 100:
                return 'Unable to create a new record: unspecified reason.';
            case 110:
                return 'Unable to create a new record: The record already exists.';
            case 120:
                return 'Unable to create a new record: missing a required value.';
            case 130:
                return 'Unable to create a new record: unique key value exists.';
            case 140:
                return 'Unable to create a new record: see previous message.';
            # Read codes
            case 200:
                return 'Unable to read the record(s): unspecified reason.';
            case 210:
                return 'Unable to read the record(s): No record exists with values given.';
            case 220:
                return 'Unable to read the record(s): a required field is missing.';
            case 230:
                return 'unable to read the record(s): invalid search term provided.';
            case 240:
                return 'Unable to read the record(s): see the previous exception message.';
            # Update Codes
            case 300:
                return 'Unable to update the record';
            case 310:
                return 'Unable to update the record: no record with that id exists.';
            case 320:
                return 'Unable to update the record: a required field is missing from the values';
            case 330:
                return 'Unable to update the record: a field given does not exist in the database.';
            case 340:
                return 'Unable to update the record: a field being changed is immutable';
            case 350:
                return 'Update not permitted.';
            # Delete codes
            case 400:
                return 'Unable to delete the record.';
            case 410:
                return 'Unable to delete the record: no record with that id exists.';
            case 420:
                return 'Unable to delete the record: missing the record primary id';
            case 430:
                return 'Unable to delete the record: has child records.';
            case 440;
                return 'Unable to delete the record: may not be deleted.';
            # Database Definition code
            case 500:
                return 'Unable to change the structure of the database.';
            case 510:
                return 'Unable to CREATE the database.';
            case 511:
                return 'Unable to ALTER the database.';
            case 512:
                return 'Unable to DROP the database.';
            case 520:
                return 'Unable to CREATE the event.';
            case 521:
                return 'Unable to ALTER the event.';
            case 522:
                return 'Unable to DROP the event.';
            case 530:
                return 'Unable to CREATE the function or procedure.';
            case 531:
                return 'Unable to ALTER the function or procedure.';
            case 532:
                return 'Unable to DROP the function or procedure.';
            case 540:
                return 'Unable to CREATE the index.';
            case 541:
                return 'Invalid error code';
            case 542:
                return 'Unable to DROP the index.';
            case 550:
                return 'Unable to CREATE the logfile group.';
            case 551:
                return 'Unable to ALTER the logfile group.';
            case 552:
                return 'Unable to DROP the logfile group.';
            case 560:
                return 'Unable to CREATE the table.';
            case 561:
                return 'Unable to ALTER the table.';
            case 562:
                return 'Unable to DROP the table.';
            case 564:
                return 'Unable to RENAME the table.';
            case 565:
                return 'Unable to TRUNCATE the table.';
            case 570:
                return 'Unable to CREATE the trigger.';
            case 571:
                return 'Unable to ALTER the trigger.';
            case 572:
                return 'Unable to DROP the trigger.';
            case 580:
                return 'Unable to CREATE the view.';
            case 581:
                return 'Unable to ALTER the view.';
            case 582:
                return 'Unable to DROP the view.';
            ### Business Logic Errors ###
            case 600:
                return 'General Business Logic Error.';
            ### Application Rule Errors ###
            case 700:
                return 'General Application Rule Error.';
            ### Generic Errors ###
            case 900:
                return 'General Error, see error message';
            case 999:
                return 'Unknown Error.';
            default:
                return parent::getCodeText($code);

        }
    }

}
