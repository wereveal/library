<?php
/**
 * Class ExceptionHelper
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Class ExceptionHelper - Helps developer with exception error codes.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.2.0
 * @date    2018-04-26 07:01:46
 * @change_log
 * - v1.2.0 additional message to code                                                - 2018-04-26 wer
 * - v1.1.0 additional error code and message                                         - 2018-04-03 wer
 * - v1.0.0 Initial version                                                           - 2017-12-12 wer
 */
class ExceptionHelper
{
    /**
     * Gets the code number for a generic error.
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumber($failure_string = '')
    {
        $failure_string = self::fixFailureString($failure_string);
        switch ($failure_string) {
            case 'business':
                return 600;
            case 'application':
                return 700;
            case 'instance':
                return 800;
            case 'general':
                return 900;
            default:
                return 999;
        }
    }

    /**
     * Gets the code number for a factory error.
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumberFactory($failure_string = '')
    {
        $failure_string = self::fixFailureString($failure_string);
        switch ($failure_string) {
            case 'start':
                return 10;
            case 'clone':
                return 20;
            case 'invalid_file_type':
                return 30;
            case 'no_configuration':
                return 40;
            case 'instance':
                return 100;
            case 'instance_objects':
                return 110;
            default:
                return self::getCodeNumber($failure_string);
        }
    }

    /**
     * Gets the code number for the model exception.
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumberModel($failure_string = '')
    {
        $failure_string = self::fixFailureString($failure_string);
        switch ($failure_string) {
            case 'create':
                return 1;
            case 'read':
                return 2;
            case 'update':
                return 3;
            case 'delete':
                return 4;
            case 'operation':
                return 10;
            case 'connect':
                return 11;
            case 'transaction_start':
                return 12;
            case 'transaction_commit':
                return 13;
            case 'transaction_rollback':
                return 14;
            case 'prepare':
                return 15;
            case 'execute':
                return 16;
            case 'pdo':
                return 17;
            case 'pdostatement':
                return 18;
            case 'structure':
                return 19;
            case 'missing_values':
                return 20;
            case 'invalid_values':
                return 22;
            case 'missing_primary_key':
                return 25;
            case 'unique_exists':
                return 27;
            case 'record_not_found':
                return 30;
            case 'records_not_found':
                return 31;
            case 'record_exists':
                return 32;
            case 'record_immutable':
                return 34;
            case 'record_immutable_undetermined':
                return 35;
            case 'has_children':
                return 36;
            case 'too_many_records':
                return 37;
            case 'field_missing':
                return 40;
            case 'field_not_valid':
                return 42;
            case 'field_value_immutable':
                return 44;
            case 'not_permitted':
                return 50;
            case 'see_previous':
                return 99;
            # Create Codes
            case 'create_unknown':
            case 'create_unspecified':
                return 110;
            case 'create_missing_value':
            case 'create_missing_values':
                return 120;
            case 'create_invalid_value':
                return 122;
            case 'create_unique_key_exists':
                return 127;
            case 'create_record_not_created':
                return 130;
            case 'create_record_exists':
                return 132;
            case 'create_not_permitted':
                return 150;
            case 'create_see_previous':
                return 199;
            # Read codes
            case 'read_unknown':
            case 'read_unspecified':
                return 210;
            case 'read_missing_value':
            case 'read_missing_values':
                return 220;
            case 'read_invalid_value':
                return 222;
            case 'read_no_records':
                return 230;
            case 'read_too_many_records':
                return 237;
            case 'read_not_permitted':
                return 250;
            case 'read_see_previous':
                return 299;
            # Update codes
            case 'update_unknown':
            case 'update_unspecified':
                return 310;
            case 'update_invalid_field':
                return 319;
            case 'update_missing_value':
            case 'update_missing_values':
                return 320;
            case 'update_no_records':
                return 330;
            case 'update_immutable':
                return 344;
            case 'update_not_permitted':
                return 350;
            case 'update_see_previous':
                return 399;
            # delete codes
            case 'delete_unknown':
            case 'delete_unspecified':
                return 410;
            case 'delete_missing_value':
                return 420;
            case 'delete_missing_primary':
                return 425;
            case 'delete_no_record':
                return 430;
            case 'delete_immutable':
                return 434;
            case 'delete_immutable_unknown':
                return 435;
            case 'delete_has_children':
                return 436;
            case 'delete_not_permitted':
                return 450;
            case 'delete_see_previous':
                return 499;
            # Definition Codes
            case 'definition_change_structure_failure':
                return 500;
            case 'definition_create_database':
                return 510;
            case 'definition_alter_database':
                return 511;
            case 'definition_drop_database':
                return 512;
            case 'definition_create_event':
                return 520;
            case 'definition_alter_event':
                return 521;
            case 'definition_drop_event':
                return 522;
            case 'definition_create_function':
            case 'definition_create_procedure':
                return 530;
            case 'definition_alter_function':
            case 'definition_alter_procedure':
                return 531;
            case 'definition_drop_function':
            case 'definition_drop_procedure':
                return 532;
            case 'definition_create_index':
                return 540;
            case 'definition_alter_index':
                return 541;
            case 'definition_drop_index':
                return 542;
            case 'definition_create_logfile':
                return 550;
            case 'definition_alter_logfile':
                return 551;
            case 'definition_drop_logfile':
                return 552;
            case 'definition_create_table':
                return 560;
            case 'definition_alter_table':
                return 561;
            case 'definition_drop_table':
                return 562;
            case 'definition_rename_table':
                return 564;
            case 'definition_truncate_table':
                return 565;
            case 'definition_create_trigger':
                return 570;
            case 'definition_alter_trigger':
                return 571;
            case 'definition_drop_trigger':
                return 572;
            case 'definition_create_view':
                return 580;
            case 'definition_alter_view':
                return 581;
            case 'definition_drop_view':
                return 582;
            default:
                return self::getCodeNumber($failure_string);
        }
    }

    /**
     * Gets the text for the error code provided for generic exceptions.
     * @param int $code
     * @return string
     */
    public static function getCodeText($code = -1)
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

    /**
     * Gets the text for the error code provided for factory exceptions.
     * @param int $code
     * @return string
     */
    public static function getCodeTextFactory($code = -1)
    {
        switch ($code) {
            case 10:
                return 'Unable to start the factory.';
            case 20:
                return '__clone not allowed for this factory.';
            case 30:
                return 'Invalid file type for configuration.';
            case 40:
                return 'Unable to get configuration for factory.';
            case 100:
                return 'Factory unable to create the object instance.';
            case 110:
                return 'Factory unable to create an object needed to create the object instance.';
            default:
                return self::getCodeText($code);
        }
    }

    /**
     * Gets the text for the error code provided for model exceptions.
     * @param int $code
     * @return string
     */
    public static function getCodeTextModel($code = -1)
    {
        switch ($code) {
            # Generic Database failures
            case 10:
                return 'Unable to do the database operation';
            case 11:
                return 'Unable to connect to the database';
            case 12:
                return 'Unable to start a transaction.';
            case 13:
                return 'Unable to commit a transaction.';
            case 14:
                return 'Unable to rollback a transaction.';
            case 15:
                return 'Unable to prepare the statement.';
            case 16:
                return 'Unable to execute the prepared statement.';
            case 17:
                return 'Unable to do a PDO operation.';
            case 18:
                return 'Unable to do a PDOStatement operation.';
            case 19:
                return 'Invalid database structure';
            case 20:
                return 'Missing required values';
            case 22:
                return 'Invalid values.';
            case 25:
                return 'Missing required primary key';
            case 27:
                return 'Unique key value exists';
            case 30:
                return 'Record(s) not found';
            case 31:
                return 'Records not found';
            case 32:
                return 'Record already exits';
            case 34:
                return 'Record immutable';
            case 40:
                return 'Required field not provided';
            case 42:
                return 'Field specified does not exist';
            case 44:
                return 'Field value is immutable';
            case 50:
                return 'Operation not permitted';
            case 99:
                return 'see previous message';
            # Create Codes
            case 110:
                return 'Unable to create a new record: unspecified reason.';
            case 120:
                return 'Unable to create a new record: missing a required value.';
            case 122:
                return 'Unable to create a new record: invalid value provided.';
            case 127:
                return 'Unable to create a new record: unique key value exists.';
            case 130:
                return 'Unable to create a new record: did not create a new primary index.';
            case 132:
                return 'Unable to create a new record: The record already exists.';
            case 150:
                return 'Unable to create a new record: the operation was not permitted.';
            case 199:
                return 'Unable to create a new record: see previous message.';
            # Read codes
            case 210:
                return 'Unable to read the record(s): unspecified reason.';
            case 220:
                return 'Unable to read the record(s): a required field is missing.';
            case 222:
                return 'unable to read the record(s): invalid search term provided.';
            case 230:
                return 'Unable to read the record(s): No record exists with values given.';
            case 250:
                return 'Unable to read the record(s): the opperation was not permitted.';
            case 299:
                return 'Unable to read the record(s): see the previous exception message.';
            # Update Codes
            case 310:
                return 'Unable to update the record';
            case 319:
                return 'Unable to update the record: a field given does not exist in the database.';
            case 320:
                return 'Unable to update the record: a required field is missing from the values';
            case 330:
                return 'Unable to update the record: no record with that id exists.';
            case 344:
                return 'Unable to update the record: a field being changed is immutable';
            case 350:
                return 'Unable to update the record: Update not permitted.';
            case 399:
                return 'Unable to update the record: see previous.';
            # Delete codes
            case 410:
                return 'Unable to delete the record.';
            case 420:
                return 'Unable to delete the record: missing the record primary id';
            case 430:
                return 'Unable to delete the record: no record with that id exists.';
            case 434:
                return 'Unable to delete the record: record is immutable.';
            case 435:
                return 'Unable to delete the record: unable to determine if the record is immutable.';
            case 436:
                return 'Unable to delete the record: has child records.';
            case 450;
                return 'Unable to delete the record: may not be deleted.';
            case 499;
                return 'Unable to delete the record: see previous error message.';
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
            default:
                return self::getCodeText($code);
        }
    }

    /**
     * Fixes the string to be compatible with methods that use it.
     * @param string $failure_string
     * @return string
     */
    public static function fixFailureString($failure_string = '')
    {
        return Strings::makeAlphanumericPlus(strtolower($failure_string));
    }
}
