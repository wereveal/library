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
 * @version 2.0.0
 * @date    2021-11-29 16:17:56
 * @change_log
 * - v2.0.0 - updated to php8, fixed bug    - 2021-11-29 wer
 * - v1.1.0 - changed mime.type file source - 2020-10-07 wer
 * - v1.0.2 - bug fix                       - 2018-04-18 wer
 * - v1.0.0 - Initial version               - 2017-11-10 wer
 */
class MimeTypeHelper
{
    /**
     * Gets the extensions that are associated with a mime type.
     * Since multiple extensions can be associated with a mime type, must return an array.
     *
     * @param string $mime_type
     * @return mixed
     */
    public static function getExtensionFromMime(string $mime_type = ''): mixed
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
     * @return string|bool
     */
    public static function getMimeFromFile(string $file_with_path = ''): string|bool
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
    public static function getMimeFromFilename(string $filename = ''): int|string
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
    public static function getMimeFromExtension($ext): int|string
    {
        if ($ext === '') {
            return '';
        }
        $a_mime_types = self::mapMimeToExtension();
        foreach ($a_mime_types as $mime_type => $a_extensions) {
            if (in_array($ext, $a_extensions)) {
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
    public static function isMimeForExtension(string $mime_type = '', string $ext = ''):bool
    {
        if ($mime_type === '' || $ext === '') {
            return false;
        }
        $a_extensions = self::getExtensionFromMime($mime_type);
        return in_array($ext, $a_extensions);
    }

    /**
     * Determines if the extension has the mime type.
     *
     * @param string $mime_type
     * @param string $ext
     * @return bool
     */
    public static function isExtensionForMime(string $mime_type = '', string $ext = ''):bool
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
        $mime_type_path = LIBRARY_PATH . '/resources/assets/files/mime.types';
        if (file_exists($mime_type_path)) {
            $r_file = fopen($mime_type_path, 'rb');
            $a_map = [];
            while (($line = fgets($r_file)) !== false) {
                if (!str_contains($line, '#')) {
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

    /**
     * Gets the latest version of the mime.types file from Apache.
     *
     * @param string $url   Optional, if desires to use a different file than from Apache.
     */
    public static function updateMimeFile(string $url):void
    {
        if (empty($url)) {
            $url = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
        }
        $mimetypes = file_get_contents($url);
        file_put_contents(LIBRARY_PATH . '/resources/assets/files/mime.types', $mimetypes);
    }
}
