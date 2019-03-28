<?php
/**
 * Class MimeTypeHelper
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Class MimeTypeHelper - Helps with mime types.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v1.0.2
 * @date    2018-04-18 14:26:04
 * @change_log
 * - v1.0.2 - bug fix                - 2018-04-18 wer
 * - v1.0.0 - Initial version        - 2017-11-10 wer
 */
class MimeTypeHelper
{
    /**
     * Gets the extensions that are associated with a mime type.
     * Since multiple extensions can be associated with a mime type, must return an array.
     *
     * @param string $mime_type
     * @return array|mixed
     */
    public static function getExtensionFromMime($mime_type = '')
    {
        if ($mime_type === '') {
            return [];
        }
        $a_mime_types = self::mapMimeToExtension();
        return $a_mime_types[$mime_type] ?? [];
    }

    /**
     * Gets the mimetype of a physical file.
     * File must exist and be in the path.
     *
     * @param string $file_with_path
     * @return mixed|string
     */
    public static function getMimeFromFile($file_with_path = '')
    {
        if (file_exists($file_with_path)) {
            $r_finfo = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($r_finfo, $file_with_path);
        }
        return '';
    }

    /**
     * Returns what the mime type should be for the given file name.
     * It should be noted that just because a file has a particular
     * extension, doesn't mean it is the mime type associated with
     * the extension.
     *
     * @param string $filename
     * @return int|string
     */
    public static function getMimeFromFilename($filename = '')
    {
        if ($filename === '') {
            return '';
        }
        $parts = explode('.', $filename);
        return self::getMimeFromExtension($parts[count($parts) - 1]);
    }

    /**
     * Returns the mimetype based on the extension.
     *
     * @param $ext
     * @return int|string
     */
    public static function getMimeFromExtension($ext) {
        if ($ext === '') {
            return '';
        }
        $a_mime_types = self::mapMimeToExtension();
        foreach ($a_mime_types as $mime_type => $a_extensions) {
            if (in_array($ext, $a_extensions, false)) {
                return $mime_type;
            }
        }
        return '';
    }

    /**
     * Determines if the extension matches the mime type.
     *
     * @param string $mime_type
     * @param string $ext
     * @return bool
     */
    public static function isMimeForExtension($mime_type = '', $ext = ''):bool
    {
        if ($mime_type === '' || $ext === '') {
            return false;
        }
        $a_extensions = self::getExtensionFromMime($mime_type);
        return in_array($ext, $a_extensions, false);
    }

    /**
     * Determines if the extension has the mime type.
     *
     * @param string $mime_type
     * @param string $ext
     * @return bool
     */
    public static function isExtensionForMime($mime_type = '', $ext = ''):bool
    {
        if ($mime_type === '' || $ext === '') {
            return false;
        }
        if (self::getMimeFromExtension($ext) === $mime_type) {
            return true;
        }
        return false;
    }

    /**
     * Reads a file of mime types and puts them into an array.
     *
     * @return array
     */
    public static function mapMimeToExtension():array
    {
        $mime_type_path = PUBLIC_PATH . '/assets/vendor/jquery-ui/node_modules/testswarm/node_modules/request/node_modules/mime/types/mime.types';
        if (file_exists($mime_type_path)) {
            $r_file = fopen($mime_type_path, 'rb');
            $a_map = [];
            while (($line = fgets($r_file)) !== false) {
                if (strpos($line, '#') === false) {
                    $line = preg_replace('/(\t+)/', '|', $line);
                    $parts = explode('|', $line);
                    $a_ext = explode(' ', $parts[1]);
                    $a_map[$parts[0]] = $a_ext;
                }
            }
            return $a_map;
        }

        return [];
    }
}
