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
 * @version 2.0.0
 * @date    2021-11-29 15:46:12
 * @change_log
 * - v2.0.0 updated to php8                                     - 2021-11-29 wer
 * - v1.2.0 additional message to code                          - 2018-04-26 wer
 * - v1.1.0 additional error code and message                   - 2018-04-03 wer
 * - v1.0.0 Initial version                                     - 2017-12-12 wer
 */
class ExceptionHelper
{
    /**
     * Gets the code number for a generic error.
     *
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumber(string $failure_string = ''):int
    {
        $failure_string = self::fixFailureString($failure_string);
        return match ($failure_string) {
            'business'                                     => 600,
            'application'                                  => 700,
            'application dependencies'                     => 710,
            'application instances', 'application objects' => 720,
            'instance'                                     => 800,
            'general'                                      => 900,
            default                                        => 999,
        };
    }

    /**
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumberCache(string $failure_string): int
    {
        $failure_string = self::fixFailureString($failure_string);
        return match ($failure_string) {
            'create'           => 1,
            'read'             => 2,
            'update'           => 3,
            'delete'           => 4,
            'database'         => 5,
            'operation'        => 10,
            'missing_value'    => 20,
            'missing_key'      => 21,
            'invalid_argument' => 22,
        };
    }

    /**
     * Gets the code number for a factory error.
     *
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumberFactory(string $failure_string = ''):int
    {
        $failure_string = self::fixFailureString($failure_string);
        return match ($failure_string) {
            'start'             => 10,
            'clone'             => 20,
            'invalid_file_type' => 30,
            'no_configuration'  => 40,
            'instance'          => 100,
            'instance_objects'  => 110,
            default             => self::getCodeNumber($failure_string),
        };
    }

    /**
     * Gets the code number for the model exception.
     *
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumberModel(string $failure_string = ''):int
    {
        $failure_string = self::fixFailureString($failure_string);
        return match ($failure_string) {
            'create'                                                    => 1,
            'read'                                                      => 2,
            'update'                                                    => 3,
            'delete'                                                    => 4,
            'operation'                                                 => 10,
            'connect'                                                   => 11,
            'transaction_start'                                         => 12,
            'transaction_commit'                                        => 13,
            'transaction_rollback'                                      => 14,
            'prepare'                                                   => 15,
            'execute'                                                   => 16,
            'pdo'                                                       => 17,
            'pdostatement'                                              => 18,
            'structure'                                                 => 19,
            'missing_values'                                            => 20,
            'invalid_values'                                            => 22,
            'missing_primary_key'                                       => 25,
            'unique_exists'                                             => 27,
            'record_not_found'                                          => 30,
            'records_not_found'                                         => 31,
            'record_exists'                                             => 32,
            'record_immutable'                                          => 34,
            'record_immutable_undetermined'                             => 35,
            'has_children'                                              => 36,
            'too_many_records'                                          => 37,
            'field_missing'                                             => 40,
            'field_not_valid'                                           => 42,
            'field_value_immutable'                                     => 44,
            'not_permitted'                                             => 50,
            'see_previous'                                              => 99,
            'create_unknown', 'create_unspecified'                      => 110,
            'create_missing_value', 'create_missing_values'             => 120,
            'create_invalid_value'                                      => 122,
            'create_unique_key_exists'                                  => 127,
            'create_record_not_created'                                 => 130,
            'create_record_exists'                                      => 132,
            'create_not_permitted'                                      => 150,
            'create_see_previous'                                       => 199,
            'read_unknown', 'read_unspecified'                          => 210,
            'read_missing_value', 'read_missing_values'                 => 220,
            'read_invalid_value'                                        => 222,
            'read_no_records'                                           => 230,
            'read_too_many_records'                                     => 237,
            'read_not_permitted'                                        => 250,
            'read_see_previous'                                         => 299,
            'update_unknown', 'update_unspecified'                      => 310,
            'update_invalid_field'                                      => 319,
            'update_missing_value', 'update_missing_values'             => 320,
            'update_missing_primary'                                    => 325,
            'update_no_records'                                         => 330,
            'update_immutable'                                          => 344,
            'update_not_permitted'                                      => 350,
            'update_see_previous'                                       => 399,
            'delete_unknown', 'delete_unspecified'                      => 410,
            'delete_missing_value'                                      => 420,
            'delete_missing_primary'                                    => 425,
            'delete_no_record'                                          => 430,
            'delete_immutable'                                          => 434,
            'delete_immutable_unknown'                                  => 435,
            'delete_has_children'                                       => 436,
            'delete_not_permitted'                                      => 450,
            'delete_see_previous'                                       => 499,
            'definition_change_structure_failure'                       => 500,
            'definition_create_database'                                => 510,
            'definition_alter_database'                                 => 511,
            'definition_drop_database'                                  => 512,
            'definition_create_event'                                   => 520,
            'definition_alter_event'                                    => 521,
            'definition_drop_event'                                     => 522,
            'definition_create_function', 'definition_create_procedure' => 530,
            'definition_alter_function', 'definition_alter_procedure'   => 531,
            'definition_drop_function', 'definition_drop_procedure'     => 532,
            'definition_create_index'                                   => 540,
            'definition_alter_index'                                    => 541,
            'definition_drop_index'                                     => 542,
            'definition_create_logfile'                                 => 550,
            'definition_alter_logfile'                                  => 551,
            'definition_drop_logfile'                                   => 552,
            'definition_create_table'                                   => 560,
            'definition_alter_table'                                    => 561,
            'definition_drop_table'                                     => 562,
            'definition_rename_table'                                   => 564,
            'definition_truncate_table'                                 => 565,
            'definition_create_trigger'                                 => 570,
            'definition_alter_trigger'                                  => 571,
            'definition_drop_trigger'                                   => 572,
            'definition_create_view'                                    => 580,
            'definition_alter_view'                                     => 581,
            'definition_drop_view'                                      => 582,
            default                                                     => self::getCodeNumber($failure_string),
        };
    }

    /**
     * Gets the code number for service exception.
     *
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumberService(string $failure_string = ''):int
    {
        return match ($failure_string) {
            'service start' => 10,
            'service clone' => 20,
            default         => self::getCodeNumber($failure_string),
        };
    }

    /**
     * Returns the exception code number for a view.
     *
     * @param string $failure_string
     * @return int
     */
    public static function getCodeNumberView(string $failure_string = ''):int
    {
        return match ($failure_string) {
            'view'                           => '700',
            'view dependencies'              => 710,
            'view instances', 'view objects' => 720,
            'view twig'                      => 750,
            default                          => self::getCodeNumber($failure_string),
        };
    }

    /**
     * Gets the text for the error code provided for generic exceptions.
     *
     * @param int $code
     * @return string
     */
    public static function getCodeText(int $code = -1):string
    {
        return match ($code) {
            600     => 'General Business Logic Error.',
            700     => 'General Application Rule Error.',
            710     => 'Unable to load required dependencies.',
            720     => 'Unable to create required instance.',
            800     => 'Unable to create the instance',
            900     => 'General Error, see error message',
            999     => 'Unspecified Error.',
            default => 'Unspecified error',
        };
    }

    /**
     * @param int $code
     * @return string
     */
    public static function getCodeTextCache(int $code = -1): string
    {
        return match ($code) {
            1   => 'Create Error',
            2   => 'Read Error',
            3   => 'Update Error',
            4   => 'Delete Error',
            5   => 'Unknown Database Error',
            10  => 'Operation Error',
            20  => 'Missing Required Value',
            21  => 'Missing Key Value',
            22  => 'Invalid Argument',
            default => self::getCodeText($code)
        };
    }
    /**
     * Gets the text for the error code provided for factory exceptions.
     *
     * @param int $code
     * @return string
     */
    public static function getCodeTextFactory(int $code = -1):string
    {
        return match ($code) {
            10      => 'Unable to start the factory.',
            20      => '__clone not allowed for this factory.',
            30      => 'Invalid file type for configuration.',
            40      => 'Unable to get configuration for factory.',
            100     => 'Factory unable to create the object instance.',
            110     => 'Factory unable to create an object needed to create the object instance.',
            default => self::getCodeText($code),
        };
    }

    /**
     * Gets the text for the error code provided for model exceptions.
     *
     * @param int $code
     * @return string
     */
    public static function getCodeTextModel(int $code = -1):string
    {
        return match ($code) {
            10      => 'Unable to do the database operation',
            11      => 'Unable to connect to the database',
            12      => 'Unable to start a transaction.',
            13      => 'Unable to commit a transaction.',
            14      => 'Unable to rollback a transaction.',
            15      => 'Unable to prepare the statement.',
            16      => 'Unable to execute the prepared statement.',
            17      => 'Unable to do a PDO operation.',
            18      => 'Unable to do a PDOStatement operation.',
            19      => 'Invalid database structure',
            20      => 'Missing required values',
            22      => 'Invalid values.',
            25      => 'Missing required primary key',
            27      => 'Unique key value exists',
            30      => 'Record(s) not found',
            31      => 'Records not found',
            32      => 'Record already exits',
            34      => 'Record immutable',
            40      => 'Required field not provided',
            42      => 'Field specified does not exist',
            44      => 'Field value is immutable',
            50      => 'Operation not permitted',
            99      => 'see previous message',
            110     => 'Unable to create a new record: unspecified reason.',
            120     => 'Unable to create a new record: missing a required value.',
            122     => 'Unable to create a new record: invalid value provided.',
            127     => 'Unable to create a new record: unique key value exists.',
            130     => 'Unable to create a new record: did not create a new primary index.',
            132     => 'Unable to create a new record: The record already exists.',
            150     => 'Unable to create a new record: the operation was not permitted.',
            199     => 'Unable to create a new record: see previous message.',
            210     => 'Unable to read the record(s):unspecified reason.',
            220     => 'Unable to read the record(s):a required field is missing.',
            222     => 'unable to read the record(s):invalid search term provided.',
            230     => 'Unable to read the record(s):No record exists with values given.',
            250     => 'Unable to read the record(s):the opperation was not permitted.',
            299     => 'Unable to read the record(s):see the previous exception message.',
            310     => 'Unable to update the record',
            319     => 'Unable to update the record: a field given does not exist in the database.',
            320     => 'Unable to update the record: a required field is missing from the values',
            325     => 'Unable to update the record: the primary key field is missing from the values',
            330     => 'Unable to update the record: no record with that id exists.',
            344     => 'Unable to update the record: a field being changed is immutable',
            350     => 'Unable to update the record: Update not permitted.',
            399     => 'Unable to update the record: see previous.',
            410     => 'Unable to delete the record.',
            420     => 'Unable to delete the record: missing the record primary id',
            430     => 'Unable to delete the record: no record with that id exists.',
            434     => 'Unable to delete the record: record is immutable.',
            435     => 'Unable to delete the record: unable to determine if the record is immutable.',
            436     => 'Unable to delete the record: has child records.',
            450     => 'Unable to delete the record: may not be deleted.',
            499     => 'Unable to delete the record: see previous error message.',
            500     => 'Unable to change the structure of the database.',
            510     => 'Unable to CREATE the database.',
            511     => 'Unable to ALTER the database.',
            512     => 'Unable to DROP the database.',
            520     => 'Unable to CREATE the event.',
            521     => 'Unable to ALTER the event.',
            522     => 'Unable to DROP the event.',
            530     => 'Unable to CREATE the function or procedure.',
            531     => 'Unable to ALTER the function or procedure.',
            532     => 'Unable to DROP the function or procedure.',
            540     => 'Unable to CREATE the index.',
            541     => 'Invalid error code',
            542     => 'Unable to DROP the index.',
            550     => 'Unable to CREATE the logfile group.',
            551     => 'Unable to ALTER the logfile group.',
            552     => 'Unable to DROP the logfile group.',
            560     => 'Unable to CREATE the table.',
            561     => 'Unable to ALTER the table.',
            562     => 'Unable to DROP the table.',
            564     => 'Unable to RENAME the table.',
            565     => 'Unable to TRUNCATE the table.',
            570     => 'Unable to CREATE the trigger.',
            571     => 'Unable to ALTER the trigger.',
            572     => 'Unable to DROP the trigger.',
            580     => 'Unable to CREATE the view.',
            581     => 'Unable to ALTER the view.',
            582     => 'Unable to DROP the view.',
            default => self::getCodeText($code),
        };
    }

    /**
     * Gets the text for the service exception code.
     *
     * @param int $code
     * @return string
     */
    public static function getCodeTextService(int $code = -1):string
    {
        return match ($code) {
            10      => 'Unable to start the service.',
            20      => '__clone not allowed for this service.',
            default => self::getCodeText($code),
        };
    }

    /**
     * Gets the text for the error code provided for view exceptions.
     *
     * @param int $code
     * @return string
     */
    public static function getCodeTextView(int $code = -1):string
    {
        return match ($code) {
            700     => 'General View Exception.',
            710     => 'Unable to load required dependencies.',
            720     => 'Unable to create required instances/objects.',
            750     => 'A Twig exception occurred.',
            default => self::getCodeText($code),
        };
    }

    /**
     * Fixes the string to be compatible with methods that use it.
     *
     * @param string $failure_string
     * @return string
     */
    public static function fixFailureString(string $failure_string = ''):string
    {
        return Strings::makeAlphanumericPlus(strtolower($failure_string));
    }
}
