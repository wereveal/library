<?php /** @noinspection CallableParameterUseCaseInTypeContextInspection */

/**
 * Class DatesTimes
 *
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

use DateInterval;
use DateTime;
use DateTimeZone;
use Error;
use Exception;

/**
 * Class DatesTimes - provides a lot of normal date time functionality.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v4.0.0
 * @date    2018-07-02 16:28:09
 * @change_log
 * - v4.0.0 - Refactored and tested good                - 2018-07-02 wer
 * - v3.1.0 - new method to convert timestamp to Y-m-d  - 2017-11-28 wer
 * - v3.0.1 - moved to Ritc\Library\Helper namespace    - 11/15/2014 wer
 * - v3.0.0 - FIG standards (mostly)
 */
class DatesTimes
{
    /**
     * Changes the timestamp to midnight, keeping the same date.
     *
     * @param string $timestamp
     * @return int
     */
    public static function changeTimestampToMidnight(string $timestamp = ''):int
    {
        if ($timestamp === '') {
            $ts = time();
        }
        elseif (self::isUnixTimestamp($timestamp) === false) {
            $ts = strtotime($timestamp);
        }
        else {
            $ts = (int) $timestamp;
        }
        $month = date('m', $ts);
        $day   = date('d', $ts);
        $year  = date('Y', $ts);
        $return_this = mktime(0, 0, 0, $month, $day, $year);
        return $return_this < 0 || $return_this === false
            ? 0
            : $return_this;
    }

    /**
     * Determines the difference in days.
     *
     * @param string $start_date
     * @param string $end_date
     * @return bool|string
     */
    public static function diffInDays(string $start_date = '', string $end_date = ''):string
    {
        $interval = self::getInterval($start_date, $end_date);
        if ($interval !== null) {
            return $interval->format('%r%a');
        }
        return '0';
    }

    /**
     * Determines the difference in months.
     *
     * @param string $start_date
     * @param string $end_date
     * @return bool|string
     */
    public static function diffInMonths(string $start_date = '', string $end_date = ''):string
    {
        $o_int = self::getInterval($start_date, $end_date);
        if ($o_int !== null) {
            return $o_int->format('%r') . (
                    ($o_int->y * 12) +
                    $o_int->m
                );
        }
        return '0';
    }

    /**
     * Determines the difference in years.
     *
     * @param string $start_date
     * @param string $end_date
     * @return bool|string
     */
    public static function diffInYears(string $start_date = '', string $end_date = ''):string
    {
        $o_int = self::getInterval($start_date, $end_date);
        if ($o_int !== null) {
            return $o_int->format('%r') . ($o_int->y + (($o_int->m/12) + ($o_int->d/30)));
        }
        return '0';
    }

    /**
     * Determines the difference in hours.
     *
     * @param string $start_time
     * @param string $end_time
     * @return bool|string
     */
    public static function diffInHours(string $start_time = '', string $end_time = ''):string
    {
        $o_int = self::getInterval($start_time, $end_time);
        if ($o_int !== null) {
            return $o_int->format('%r') . (
                ($o_int->y * 365 * 24) +
                ($o_int->m * 30 * 24) +
                ($o_int->d * 24) +
                $o_int->h
            );
        }
        return '0';
    }

    /**
     * Determines the difference in minutes.
     *
     * @param string $start_time
     * @param string $end_time
     * @return bool|string
     */
    public static function diffInMinutes(string $start_time = '', string $end_time = ''):string
    {
        $o_int = self::getInterval($start_time, $end_time);
        if ($o_int !== null) {
            $minus = $o_int->format('%r');
            /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
            return $minus . (
                ($o_int->y * 365 * 24 * 60) +
                ($o_int->m * 30 * 24 * 60) +
                ($o_int->d * 24 * 60) +
                ($o_int->h * 60) +
                $o_int->i
            );
        }
        return '0';
    }

    /**
     * Determines the difference in seconds.
     *
     * @param string $start_time
     * @param string $end_time
     * @return bool|string
     */
    public static function diffInSeconds(string $start_time = '', string $end_time = ''):string
    {
        $o_int = self::getInterval($start_time, $end_time);
        if ($o_int !== null) {
            // print_r($o_int);
            $minus = $o_int->format('%r');
            /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
            return $minus . (
                ($o_int->y * 365 * 24 * 60 * 60) +
                ($o_int->m * 30 * 24 * 60 * 60) +
                ($o_int->d * 24 * 60 * 60) +
                ($o_int->h * 60 * 60) +
                ($o_int->i * 60) +
                $o_int->s
            );
        }
        return '0';
    }

    /**
     * Changes a time string in 24hr format to 12hr format.
     *
     * @param string    $time_string
     * @param bool|true $include_seconds
     * @param bool|true $include_meridiem
     * @return string
     */
    public static function change24hTo12h(string $time_string = '', $include_seconds = true, $include_meridiem = true):string
    {
        if ($include_seconds) {
            $time_format = $include_meridiem ? 'g:i:s a' : 'g:i:s' ;
        }
        else {
            $time_format = $include_meridiem ? 'g:i a' : 'g:i' ;
        }
        try {
            $timestring = empty($time_string)
                ? date('m/d/Y H:i:s e')
                : $time_string;
        }
        catch (Error $e) {
            $timestring = '';
        }
        if (self::isUnixTimestamp($timestring)) {
            $ts = self::convertToUnixTimestamp($timestring);
            try {
                $timestring = date('Y-m-d H:i:s e', $ts);
            }
            catch (Error $e) {
                $timestring = '';
            }
        }
        try {
            $o_time = new DateTime($timestring, new DateTimeZone(date('e')));
            if ($o_time !== false) {
                return $o_time->format($time_format);
            }
            return '';
        }
        catch (Exception $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('Caught exception: ' . $e->getMessage() . ' from: ' . __METHOD__ . '.' . __LINE__);
            return '';
        }
    }

    /**
     * Converts a date to a specified date format.
     * Stub for self::convertDateTimeWith, legacy method.
     *
     * @param string $date_format
     * @param string $timestamp
     * @param string $timezone
     * @deprecated v3.2.0
     * @return null|string
     */
    public static function convertDateWith(string $date_format = '', string $timestamp = '', string $timezone = ''):string
    {
        return self::convertDateTimeWith($date_format, $timestamp, $timezone);
    }

    /**
     * Converts a date to a specified date format.
     *
     * @param string $date_format Optional, defaults to \DateTime::ATOM
     * @param string $timestamp   Optional, defaults to now, can be either valid
     *                            date or UNIX timestamp
     * @param string $timezone    Optional, defaults to server tz. Converts
     *                            server date time tz to provided tz.
     * @return string|null
     */
    public static function convertDateTimeWith(string $date_format = '', string $timestamp = '', string $timezone = ''):string
    {
        $date_format = self::isValidDateFormat($date_format)
            ? $date_format
            : DateTime::ATOM;
        if ($timestamp === '') {
            try {
                $date = date($date_format);
            }
            catch (Error $e) {
                $date = $timestamp;
            }
        }
        elseif (self::isUnixTimestamp($timestamp)) {
            try {
                $date = date($date_format, (int) $timestamp);
            }
            catch (Error $e) {
                $date = $timestamp;
            }
        }
        else {
            $date = $timestamp;
        }
        $o_tz = self::isValidTimezone($timezone)
            ? new DateTimeZone($timezone)
            : false;
        try {
            $o_time = new DateTime($date);
        }
        catch (Exception $e) {
            return '';
        }
        if ($o_tz) {
            $o_time->setTimezone($o_tz);
        }
        return $o_time->format($date_format);
    }

    /**
     * Returns the day name of a date.
     *
     * @param string $timestamp
     * @param string $format
     * @return bool|string
     */
    public static function getDayName(string $timestamp = '', string $format = 'long')
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return ($format === 'short' ? date('D', $ts) : date('l', $ts));
    }

    /**
     * Returns the day number of a date.
     *
     * @param string $timestamp Optional, defaults to now().
     * @param string $format    Optional, defaults to 'd', i.e. leading zero.
     * @return string
     */
    public static function getDayNumber(string $timestamp = '', string $format = 'default'):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        switch($format) {
            case 'j':
            case 'short': // no leading zero
                try {
                    $return_this = date('j', $ts);
                }
                catch (Error $e) {
                    $return_this = 'error';
                }
                break;
            case 'z':
            case 'doy': // day of year
                try {
                    $return_this = date('z', $ts);
                }
                catch (Error $e) {
                    $return_this = 'error';
                }
                break;
            case 'd':
            case 'default': // leading zero
            default:
                try {
                    $return_this = date('d', $ts);
                }
                catch (Error $e) {
                    $return_this = 'error';
                }
        }
        return $return_this;
    }

    /**
     * Gets the interval between two dates.
     *
     * @param string $start_date
     * @param string $end_date
     * @return DateInterval
     */
    public static function getInterval(string $start_date = '', string $end_date = ''): DateInterval
    {
        try {
            $start_date = $start_date === '' ? date('m/d/Y H:i:s') : $start_date;
            $end_date   = $end_date === ''   ? date('m/d/Y H:i:s') : $end_date;
            if (self::isUnixTimestamp($start_date)) {
                $start_date = date('m/d/Y H:i:s', (int) $start_date);
            }
            else {
                $start_date = date('m/d/Y H:i:s', strtotime($start_date));
            }
            if (self::isUnixTimestamp($end_date)) {
                $end_date = date('m/d/Y H:i:s', (int) $end_date);
            }
            else {
                $end_date = date('m/d/Y H:i:s', strtotime($end_date));
            }
        }
        catch (Error $e) {
            return null;
        }
        try {
            $o_start = new DateTime($start_date);
            $o_end   = new DateTime($end_date);
            return $o_start->diff($o_end);
        }
        catch (Exception $e) {
            return null;
        }
    }

    /**
     * Converts a timestamp to long date time format.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToLongDateTime(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('l, F jS, Y g:i a', $ts);
        }
        catch (Error $e) {
            return null;
        }
    }

    /**
     * Converts a timestamp to long date format.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToLongDate(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('l, F jS, Y', $ts);
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Returns am or pm of a timestamp.
     *
     * @param string $timestamp Optional, if omitted or invalid returns date('A'||'a').
     * @param bool $upper_case  Optional, defaults to false, use lower case.
     * @return string
     */
    public static function getMeridiem(string $timestamp = '', bool $upper_case = false):string
    {
        $time_format = $upper_case ? 'A' : 'a' ;
        if (self::isUnixTimestamp($timestamp)) {
            return date($time_format, (int) $timestamp);
        }
        try {
            $o_date = new DateTime($timestamp);
            $rt = $o_date->format($time_format);
            return $rt !== false ? $rt : date($time_format);
        }
        catch (Exception $e) {
            return date($time_format);
        }
    }

    /**
     * Converts timestamp to a month.
     *
     * @param string $timestamp
     * @param string $format
     * @return bool|string
     */
    public static function convertToMonth(string $timestamp = '', string $format = 'default')
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        switch ($format) {
            case 'F':
            case 'full':
            case 'long':
                $format = 'F';
                break;
            case 'M':
            case 'short':
                $format = 'M';
                break;
            case 'n':
            case 'int':
            case 'number':
                $format = 'n';
                break;
            case 'm':
            case 'default':
            default:
                $format = 'm';
        }
        try {
            return date($format, $ts);
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Changes the date to next day.
     *
     * @param string $timestamp
     * @param string $format
     * @return int|string
     */
    public static function convertToNextDay(string $timestamp = '', string $format = 'atom')
    {
        $ts = self::convertDateTimeWith(DateTime::ATOM, $timestamp);
        try {
            $date = new DateTime($ts);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('+1 day');
        switch ($format) {
            case 'l';
            case 'name':
                return $date->format('l');
            case 'D':
            case 'short_name':
                return $date->format('D');
            case 'd':
            case 'number':
                return $date->format('d');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(DateTime::ATOM);
            case '':
            case 'timestamp':
                return self::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (self::isValidDateFormat($format)) {
                    return $date->format($format);
                }
                return self::changeTimestampToMidnight($date->getTimestamp());
        }
    }

    /**
     * Changes the date to next month.
     *
     * @param string $timestamp
     * @param string $format
     * @return int|string
     */
    public static function convertToNextMonth(string $timestamp = '', string $format = 'timestamp')
    {
        $ts = self::convertDateTimeWith(DateTime::ATOM, $timestamp);
        try {
            $date = new DateTime($ts);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('+1 month');
        switch ($format) {
            case 'name':
                return $date->format('F');
            case 'short_name':
                return $date->format('M');
            case 'number':
                return $date->format('m');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(DateTime::ATOM);
            case '':
            case 'timestamp':
                return self::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (self::isValidDateFormat($format)) {
                    return $date->format($format);
                }
                return self::changeTimestampToMidnight($date->getTimestamp());
        }
    }

    /**
     * Converts the date to next week.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToNextWeek(string $timestamp = ''):string
    {
        $ts = self::convertDateTimeWith(DateTime::ATOM, $timestamp);
        try {
            $date = new DateTime($ts);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('+1 week');
        return $date->format('W');
    }

    /**
     * Converts the date to next year.
     *
     * @param string $timestamp
     * @param string $format
     * @return string
     */
    public static function convertToNextYear(string $timestamp = '', string $format = 'timestamp'):string
    {
        $timestamp = self::convertDateTimeWith(DateTime::ATOM, $timestamp) ?? time();
        try {
            $date = new DateTime($timestamp);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('+1 year');
        switch ($format) {
            case 'iso':
                return $date->format('o');
            case 'leap':
                return $date->format('L');
            case 'number':
                return $date->format('Y');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(DateTime::ATOM);
            case '':
            case 'timestamp':
                return (string) self::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (self::isValidDateFormat($format)) {
                    return $date->format($format);
                }
                return (string) self::changeTimestampToMidnight($date->getTimestamp());
        }
    }

    /**
     * Converts the date to the previous day.
     *
     * @param string $timestamp
     * @param string $format
     * @return int|string
     */
    public static function convertToPreviousDay(string $timestamp = '', string $format = 'timestamp')
    {
        $ts = self::convertDateTimeWith(DateTime::ATOM, $timestamp);
        try {
            $date = new DateTime($ts);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('-1 day');
        switch ($format) {
            case 'name':
                return $date->format('l');
            case 'short_name':
                return $date->format('D');
            case 'number':
                return $date->format('d');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(DateTime::ATOM);
            case '':
            case 'timestamp':
                return self::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (self::isValidDateFormat($format)) {
                    return $date->format($format);
                }
                return self::changeTimestampToMidnight($date->getTimestamp());
        }
    }

    /**
     * Converts the date to the previous month.
     *
     * @param string $timestamp
     * @param string $format
     * @return string|int
     */
    public static function convertToPreviousMonth(string $timestamp = '', string $format = 'timestamp')
    {
        $timestamp = self::convertDateTimeWith(DateTime::ATOM, $timestamp) ?? time();
        try {
            $date = new DateTime($timestamp);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('-1 month');
        switch ($format) {
            case 'name':
                return $date->format('F');
            case 'short_name':
                return $date->format('M');
            case 'number':
                return $date->format('m');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(DateTime::ATOM);
            case 'timestamp':
            default:
                return self::changeTimestampToMidnight($date->getTimestamp());
        }
    }

    /**
     * Converts the date to the previous week.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToPreviousWeek(string $timestamp = ''):string
    {
        $ts = self::convertDateTimeWith(DateTime::ATOM, $timestamp);
        try {
            $date = new DateTime($ts);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('-1 week');
        return $date->format('W');
    }

    /**
     * Converts the date to the previous year.
     *
     * @param string $timestamp
     * @param string $format
     * @return int|string
     */
    public static function convertToPreviousYear(string $timestamp = '', string $format = '')
    {
        $ts = self::convertDateTimeWith(DateTime::ATOM, $timestamp);
        try {
            $date = new DateTime($ts);
        }
        catch (Exception $e) {
            return '';
        }
        $date->modify('-1 year');
        switch ($format) {
            case 'iso':
                return $date->format('o');
            case 'leap':
                return $date->format('L');
            case 'number':
                return $date->format('Y');
            case 'short_date':
                return $date->format('m/d/Y');
            case 'atom':
                return $date->format(DateTime::ATOM);
            case '':
            case 'timestamp':
                return self::changeTimestampToMidnight($date->getTimestamp());
            default:
                if (self::isValidDateFormat($format)) {
                    return $date->format($format);
                }

                return self::changeTimestampToMidnight($date->getTimestamp());
        }
    }

    /**
     * Converts timestamp to short date format (m/d/Y).
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToShortDate(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('m/d/Y', $ts);
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Converts timestamp to short date time (m/d/Y hour:min a/p).
     *
     * @param string $timestamp
     * @return bool|string
     */
    public static function convertToShortDateTime(string $timestamp = '')
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('m/d/Y g:i a', $ts) ?? '';
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Converts the date/timestamp to sql formatted date (Y-m-d).
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToSqlDate(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('Y-m-d', $ts) ?? '';
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Convert timestamp to sql formatted timestamp.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToSqlTimestamp(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('Y-m-d H:i:s', $ts) ?? '';
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Converts timestamp to the time (hour:minute:seconds).
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToTime(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date ('H:i:s', $ts) ?? '';
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Converts timestamp to time with Abbreviated timezone (H:i:s T).
     *
     * @param string $timestamp
     * @param string $timezone
     * @return null|string
     */
    public static function convertToTimeWithTz(string $timestamp = '', string $timezone = ''):string
    {
        $time_format = 'H:i:s T';
        return self::convertDateTimeWith($time_format, $timestamp, $timezone);
    }

    /**
     * Convert timestamp to time with timezone offset (hour:min:sec+600).
     *
     * @param string $timestamp
     * @param string $timezone
     * @return null|string
     */
    public static function convertToTimeWithTzOffset(string $timestamp = '', string $timezone = ''):string
    {
        $time_format = 'H:i:sP';
        return self::convertDateTimeWith($time_format, $timestamp, $timezone);
    }

    /**
     * Convert timestring to a time string in specified format.
     *
     * @param string $time_format
     * @param string $timestamp
     * @param string $timezone
     * @return null|string
     */
    public static function convertToTimeWith(string $time_format = '',
                                             string $timestamp = '',
                                             string $timezone = ''):string
    {
        $time_format = $time_format === ''
            ? 'H:i:s'
            : $time_format;
        return self::convertDateTimeWith($time_format, $timestamp, $timezone);
    }

    /**
     * Gets date for tomorrow.
     *
     * @param string $format
     * @return string|int
     */
    public static function getTomorrow(string $format = 'm/d/Y'):string
    {
        return self::convertToNextDay(date($format), $format);
    }

    /**
     * Converts a time string to a unix timestamp.
     *
     * @param string $timestamp Optional, but silly if empty or invalid since it will just return time().
     * @return int
     */
    public static function convertToUnixTimestamp(string $timestamp = ''):int
    {
        $ts = $timestamp !== ''
            ? $timestamp
            : (string) time();
        if (self::isUnixTimestamp($ts)) {
            return (int)$ts;
        }
        if (strtotime($ts) !== false) {
            return strtotime($ts);
        }
        return (string)time();
    }

    /**
     * Converts the timestamp to the week string.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToWeek(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('W', $ts) ?? '';
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Converts the timestamp to the year.
     *
     * @param string $timestamp
     * @return bool|string
     */
    public static function convertToYear(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            return date('Y', $ts) ?? '';
        }
        catch (Error $e) {
            return '';
        }
    }

    /**
     * Returns the previous day in specified format.
     *
     * @param string $format
     * @return string
     */
    public static function getYesterday(string $format = 'Y-m-d'):string
    {
        if (!self::isValidDateFormat($format)) {
            $format = 'Y-m-d';
        }
        return self::convertToPreviousDay(date($format), $format);
    }

    /**
     * Converts the timestamp to Ymd.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToYmd(string $timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        try {
            $string = date('Ymd', $ts) ?? '';
        }
        catch (Error $e) {
            $string =  '';
        }
        return $string;
    }

    /**
     * Determines if the string is a unix timestamp.
     *
     * @param string $value
     * @return bool
     */
    public static function isUnixTimestamp(string $value = ''):bool
    {
        return ((string) (int) $value === $value)
            && ($value <= PHP_INT_MAX)
            && ($value >= 0);
    }

    /**
     * Determines if the string is a valid date format.
     *
     * @param string $date_format
     * @return bool
     */
    public static function isValidDateFormat(string $date_format = ''):bool
    {
        $a_date_info = date_parse_from_format($date_format, date($date_format));
        return !($a_date_info['error_count'] > 0);
    }

    /**
     * Determines if the string is a valid time zone.
     *
     * @param string $timezone
     * @return bool
     */
    public static function isValidTimezone(string $timezone = ''):bool
    {
        if ($timezone === '') {
            return false;
        }
        try {
            new DateTimeZone($timezone);
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }
}
