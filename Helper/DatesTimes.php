<?php /** @noinspection PhpAssignmentInConditionInspection */

/**
 * Class DatesTimes
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Class DatesTimes - provides a lot of normal date time functionality.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v3.1.0
 * @date    2017-11-28 16:14:44
 * @change_log
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
    public static function changeTimestampToMidnight($timestamp = ''):int
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
        return mktime(0, 0, 0, $month, $day, $year);
    }

    /**
     * Determines the difference in days.
     *
     * @param string $start_date
     * @param string $end_date
     * @return bool|string
     */
    public static function diffInDays($start_date = '', $end_date = '')
    {
        if ($interval = self::getInterval($start_date, $end_date)) {
            return $interval->format('%R%a');
        }
        return false;
    }

    /**
     * Determines the difference in months.
     *
     * @param string $start_date
     * @param string $end_date
     * @return bool|string
     */
    public static function diffInMonths($start_date = '', $end_date = '')
    {
        if ($interval = self::getInterval($start_date, $end_date)) {
            return $interval->format('%r%m');
        }
        return false;
    }

    /**
     * Determines the difference in years.
     *
     * @param string $start_date
     * @param string $end_date
     * @return bool|string
     */
    public static function diffInYears($start_date = '', $end_date = '')
    {
        if ($interval = self::getInterval($start_date, $end_date)) {
            return $interval->format('%R%y');
        }

        return false;
    }

    /**
     * Determines the difference in hours.
     *
     * @param string $start_time
     * @param string $end_time
     * @return bool|string
     */
    public static function diffInHours($start_time = '', $end_time = '')
    {
        if ($interval = self::getInterval($start_time, $end_time)) {
            return $interval->format('%R%h');
        }

        return false;
    }

    /**
     * Determines the difference in minutes.
     *
     * @param string $start_time
     * @param string $end_time
     * @return bool|string
     */
    public static function diffInMinutes($start_time = '', $end_time = '')
    {
        if ($interval = self::getInterval($start_time, $end_time)) {
            return $interval->format('%R%i');
        }

        return false;
    }

    /**
     * Determines the difference in seconds.
     *
     * @param string $start_time
     * @param string $end_time
     * @return bool|string
     */
    public static function diffInSeconds($start_time = '', $end_time = '')
    {
        if ($interval = self::getInterval($start_time, $end_time)) {
            return $interval->format('%R%s');
        }

        return false;
    }

    /**
     * Changes a time string in 24hr format to 12hr format.
     *
     * @param string    $time_string
     * @param bool|true $include_seconds
     * @param bool|true $include_meridiem
     * @return null|string
     */
    public static function change24hTo12h($time_string = '', $include_seconds = true, $include_meridiem = true):?string
    {
        if ($include_seconds) {
            $time_format = $include_meridiem ? 'g:i:s a' : 'g:i:s' ;
        }
        else {
            $time_format = $include_meridiem ? 'g:i a' : 'g:i' ;
        }
        $timestring = $time_string ?? @date('m/d/Y H:i:s e') ?? '';
        if (self::isUnixTimestamp($timestring)) {
            $ts = self::convertToUnixTimestamp($timestring);
            $timestring = @date('m/d/Y H:i:s e', $ts) ?? '';
        }
        try {
            $o_time = new \DateTime($timestring, new \DateTimeZone(date('e')));
            if ($o_time !== false) {
                return $o_time->format($time_format);
            }
            return null;
        }
        catch (\Exception $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('Caught exception: ' . $e->getMessage() . ' from: ' . __METHOD__ . '.' . __LINE__);
            return null;
        }
    }

    /**
     * Converts a date to a specified date format.
     *
     * @param string $date_format
     * @param string $timestamp
     * @param string $timezone
     * @return null|string
     */
    public static function convertDateWith($date_format = '', $timestamp = '', $timezone = ''):?string
    {
        $date_format = $date_format !== '' ? $date_format : 'm/d/Y' ;
        return self::convertDateTimeWith($date_format, $timestamp, $timezone);
    }

    /**
     * Converts a date to a specified date format.
     *
     * @param string $date_format
     * @param string $timestamp
     * @param string $timezone
     * @return string
     */
    public static function convertDateTimeWith($date_format = '', $timestamp = '', $timezone = ''):?string
    {
        $date_format = $date_format === '' ? \DateTime::ATOM : $date_format ;
        $date_format = self::isValidDateFormat($date_format) ? $date_format : \DateTime::ATOM;
        if ($timestamp === '') {
            $date = @date($date_format) ?? '';
        }
        elseif (self::isUnixTimestamp($timestamp)) {
            $date = @date($date_format, (int) $timestamp) ?? '';
        }
        else {
            $date = $timestamp;
        }
        $timezone = self::isValidTimezone($timezone)
            ? $timezone
            : @date('e') ?? 'America/Chicago';
        try {
            $o_time = new \DateTime($date, new \DateTimeZone($timezone));
        }
        catch (\Exception $e) {
            return '';
        }
        return $o_time->format($date_format) ?? '';
    }

    /**
     * Returns the day name of a date.
     *
     * @param string $timestamp
     * @param string $format
     * @return bool|string
     */
    public static function getDayName($timestamp = '', $format = 'short')
    {
        $ts = self::convertToUnixTimestamp((string)$timestamp);
        return ($format === 'short' ? date('D', $ts) : date('l', $ts));
    }

    /**
     * Returns the day number of a date.
     *
     * @param string $timestamp
     * @param string $format
     * @return string
     */
    public static function getDayNumber($timestamp = '', $format = 'default'):string
    {
        $ts = self::convertToUnixTimestamp((string)$timestamp);
        switch($format) {
            case 'j':
            case 'short':
                $return_this = @date('j', $ts);
                $return_this = $return_this ?? '';
                break;
            case 'z':
            case 'doy':
                $return_this = @date('z', $ts);
                $return_this = $return_this ?? '';
                break;
            case 'd':
            case 'default':
            default:
                $return_this = @date('d', $ts);
                $return_this = $return_this ?? '';
        }
        return $return_this;
    }

    /**
     * Gets the interval between two dates.
     *
     * @param string $start_date
     * @param string $end_date
     * @return bool|\DateInterval
     */
    public static function getInterval($start_date = '', $end_date = '')
    {
        $start_date = $start_date ?? @date('m/d/Y H:i:s') ?? '';
        $end_date   = $end_date ?? @date('m/d/Y H:i:s') ?? '';
        if (self::isUnixTimestamp($start_date)) {
            $start_date = @date('m/d/Y H:i:s', (int) $start_date) ?? '';
        }
        else {
            $start_date = @date('m/d/Y H:i:s', strtotime($start_date)) ?? '';
        }
        if (self::isUnixTimestamp($end_date)) {
            $end_date = @date('m/d/Y H:i:s', (int) $end_date) ?? '';
        }
        else {
            $end_date = @date('m/d/Y H:i:s', strtotime($end_date)) ?? '';
        }
        try {
            $o_start = new \DateTime($start_date);
            $o_end   = new \DateTime($end_date);
            return $o_start->diff($o_end);
        }
        catch (\Exception $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log('Caught exception: ', $e->getMessage());
            return false;
        }
    }

    /**
     * Converts a timestamp to long date time format.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToLongDateTime($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp((string)$timestamp);
        $return_this = @date('l, F dS, Y g:i a', $ts);
        return $return_this ?? '';
    }

    /**
     * Converts a timestamp to long date format.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToLongDate($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp((string)$timestamp);
        $return_this = @date('l, F dS, Y', $ts);
        return $return_this ?? '';
    }

    /**
     * Returns am or pm of a timestamp.
     *
     * @param string $timestamp
     * @param bool $upper_case
     * @return bool|string
     */
    public static function getMeridiem($timestamp = '', $upper_case = false)
    {
        $time_format = $upper_case ? 'A' : 'a' ;
        if (self::isUnixTimestamp($timestamp)) {
            return date($time_format, (int) $timestamp);
        }

        $o_date = new \DateTime($timestamp);
        return $o_date->format($time_format);
    }

    /**
     * Converts timestamp to a month.
     *
     * @param string $timestamp
     * @param string $format
     * @return bool|string
     */
    public static function convertToMonth($timestamp = '', $format = 'default')
    {
        $ts = self::convertToUnixTimestamp((string)$timestamp);
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
        return date($format, $ts);
    }

    /**
     * Changes the date to next day.
     *
     * @param string $timestamp
     * @param string $format
     * @return int|string
     */
    public static function convertToNextDay($timestamp = '', $format = '')
    {
        $ts = self::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($ts);
        $date->modify('+1 day');
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
                return $date->format(\DateTime::ATOM);
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
    public static function convertToNextMonth($timestamp = '', $format = '')
    {
        $ts = self::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($ts);
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
                return $date->format(\DateTime::ATOM);
            case 'timestamp':
            default:
                return self::changeTimestampToMidnight($date->getTimestamp());
        }
    }

    /**
     * Converts the date to next week.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToNextWeek($timestamp = ''):string
    {
        $timestamp = self::convertDateTimeWith(\DateTime::ATOM, $timestamp) ?? $timestamp;
        $date = new \DateTime($timestamp);
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
    public static function convertToNextYear($timestamp = '', $format = ''):string
    {
        $timestamp = self::convertDateTimeWith(\DateTime::ATOM, $timestamp) ?? $timestamp;
        try {
            $date = new \DateTime($timestamp);
        }
        catch (\Exception $e) {
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
                return $date->format(\DateTime::ATOM);
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
    public static function convertToPreviousDay($timestamp = '', $format = '')
    {
        $timestamp = self::convertDateTimeWith(\DateTime::ATOM, $timestamp) ?? '';
        $date = new \DateTime($timestamp);
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
                return $date->format(\DateTime::ATOM);
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
    public static function convertToPreviousMonth($timestamp = '', $format = '')
    {
        $timestamp = self::convertDateTimeWith(\DateTime::ATOM, $timestamp) ?? '';
        try {
            $date = new \DateTime($timestamp);
        }
        catch (\Exception $e) {
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
                return $date->format(\DateTime::ATOM);
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
    public static function convertToPreviousWeek($timestamp = ''):string
    {
        $timestamp = self::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
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
    public static function convertToPreviousYear($timestamp = '', $format = '')
    {
        $timestamp = self::convertDateTimeWith(\DateTime::ATOM, $timestamp);
        $date = new \DateTime($timestamp);
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
                return $date->format(\DateTime::ATOM);
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
     * @return bool|string
     */
    public static function convertToShortDate($timestamp = '')
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date('m/d/Y', $ts) ?? '';
    }

    /**
     * Converts timestamp to short date time (m/d/Y hour:min a/p).
     *
     * @param string $timestamp
     * @return bool|string
     */
    public static function convertToShortDateTime($timestamp = '')
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date('m/d/Y g:i a', $ts) ?? '';
    }

    /**
     * Converts the date/timestamp to sql formatted date (Y-m-d).
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToSqlDate($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date('Y-m-d', $ts) ?? '';
    }

    /**
     * Convert timestamp to sql formatted timestamp.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToSqlTimestamp($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date('Y-m-d H:i:s', $ts) ?? '';
    }

    /**
     * Converts timestamp to the time (hour:minute:seconds).
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToTime($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date ('H:i:s', $ts) ?? '';
    }

    /**
     * Converts time string to a time format (hour:minute:second).
     *
     * @param string $timestring
     * @return string
     */
    public static function convertToTimeFromString($timestring = ''):string
    {
        if (self::isUnixTimestamp($timestring)) {
            $unix_timestamp = (int) $timestring;
        }
        else {
            $unix_timestamp = empty($timestring)
                ? time()
                : strtotime($timestring);
        }
        return @date('H:i:s', $unix_timestamp) ?? '';
    }

    /**
     * Converts timestamp to time with timezone (H:i:s e).
     *
     * @param string $timestamp
     * @param string $timezone
     * @return null|string
     */
    public static function convertToTimeWithTz($timestamp = '', $timezone = ''):?string
    {
        $time_format = 'H:i:s e';
        return self::convertDateTimeWith($time_format, $timestamp, $timezone);
    }

    /**
     * Convert timestamp to time with timezone offset (hour:min:sec+600).
     *
     * @param string $timestamp
     * @param string $timezone
     * @return null|string
     */
    public static function convertToTimeWithTzOffset($timestamp = '', $timezone = ''):?string
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
    public static function convertToTimeWith($time_format = '', $timestamp = '', $timezone = ''):?string
    {
        $time_format = $time_format === '' ? 'H:i:s' : $time_format;
        $timezone = self::isValidTimezone($timezone) ? date('e') : $timezone;
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
     * @param string $timestamp
     * @return int
     */
    public static function convertToUnixTimestamp($timestamp = ''):int
    {
        $timestamp = $timestamp ?? (string) time();
        return (self::isUnixTimestamp($timestamp)
            ? (int) $timestamp
            : strtotime($timestamp));
    }

    /**
     * Converts the timestamp to the week string.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToWeek($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date('W', $ts) ?? '';
    }

    /**
     * Converts the timestamp to the year.
     *
     * @param string $timestamp
     * @return bool|string
     */
    public static function convertToYear($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date('Y', $ts) ?? '';
    }

    /**
     * Returns the previous day in specified format.
     *
     * @param string $format
     * @return string
     */
    public static function getYesterday(string $format = 'm/d/Y'):string
    {
        return self::convertToPreviousDay(date($format), $format);
    }

    /**
     * Converts the timestamp to Ymd.
     *
     * @param string $timestamp
     * @return string
     */
    public static function convertToYmd($timestamp = ''):string
    {
        $ts = self::convertToUnixTimestamp($timestamp);
        return @date('Ymd', $ts) ?? '';
    }

    /**
     * Determines if the string is a unix timestamp.
     *
     * @param string $value
     * @return bool
     */
    public static function isUnixTimestamp($value = ''):bool
    {
        if ($value === '') {
            return false;
        }
        return empty(strtotime($value));
    }

    /**
     * Determines if the string is a valid date format.
     *
     * @param string $date_format
     * @return bool
     */
    public static function isValidDateFormat($date_format = ''):?bool
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
    public static function isValidTimezone($timezone = ''):bool
    {
        if ($timezone === '') {
            return false;
        }
        try {
            new \DateTimeZone($timezone);
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }
}
