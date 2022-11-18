<?php

namespace Ritc\Library\Helper;

use Ritc\Library\Exceptions\CacheException;
use Ritc\Library\Interfaces\CacheHelperInterface;

/**
 * Class CacheHelper
 *
 * @date    2022-03-15 15:17:25
 * @version 1.0.0-beta.1
 * @change_log
 * - v1.0.0-beta.1 - Initial version                            - 2022-03-15 wer
 */
class CacheHelper implements CacheHelperInterface
{
    /**
     * Creates a cache key based on the path of a file.
     * Not sure when this may be needed but seems to be so.
     *
     * @param string $file_w_path
     * @return string
     * @throws CacheException
     */
    public static function createKeyFromPath(string $file_w_path): string
    {
        if (empty($file_w_path)) {
            throw new CacheException(
                'Missing file_w_path value',
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        $file_w_parts = str_replace(CACHE_PATH  . '/', '', $file_w_path);
        $file_w_parts = strtolower($file_w_parts);
        [$file_with_parts] = explode('.', $file_w_parts); // gets rid of the expires and file extension
        return str_replace('/', '.', $file_with_parts);
    }

    /**
     * Creates the file path string where the key file resides.
     *
     * @param string $key
     * @return string
     * @throws CacheException
     */
    public static function createPathFromKey(string $key): string
    {
        $file_path = CACHE_PATH;
        $key = strtolower($key);
        $file_parts = self::fetchKeyParts($key);
        foreach ($file_parts['file_dirs'] as $dir) {
            $file_path .= '/' . $dir;
        }
        if (!file_exists($file_path)
            && (!mkdir($file_path, 0755, true) && !is_dir($file_path))
        ) {
            throw new CacheException(
                'Directory missing and could not be created',
                ExceptionHelper::getCodeNumberCache('operation')
            );
        }
        return $file_path;
    }

    /**
     * Returns the path(s) inside the cache directory for a specific key.
     *
     * This is based on the philosophy of that the cache key can be multi-part
     * based on dots, e.g. first.second.third The key is turned into a file with
     * path, expirations, and file extension that looks like
     * /cache/first/second/third.expires.ext
     *
     * @param string $key
     * @return array
     * @throws CacheException
     */
    public static function fetchFilePathsFromKey(string $key): array
    {
        if (empty($key)) {
            throw new CacheException('Key is required', ExceptionHelper::getCodeNumberCache('missing_value'));
        }
        $path = CACHE_PATH; // ouch, assumes CACHE_PATH has been defined which is most likely but not guaranteed
        $a_parts = explode('.', $key);
        foreach ($a_parts as $part) {
            $path .= '/' . $part;
        }
        return glob($path . '*');
    }

    /**
     * Transverses the cache directory for the file with the key.
     *
     * @note Multiple files may have the same basename, but based on a
     * multi-part key name which creates tags/pools they can be all over the place.
     * e.g. the key one.route.group.phred would create the file in
     * CACHE_PATH . '/one/route/group/phred.$ttl.$file_type
     * the key two.route.group.phred would create the file in
     * CACHE_PATH . '/two/route/group/phred.$ttl.$file_type
     * the key one.route.fred.phred would create the file in
     * CACHE_PATH . '/one/route/fred/phred.$ttl.$file_type
     * Three phred.$ttl.$file_type files but in different locations.
     *
     *
     * @param string $key
     * @return array
     * @throws CacheException
     */
    public static function fileInfoByKey(string $key): array
    {
        if (empty($key)) {
            throw new CacheException(
                'Missing key', 
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        $path = CACHE_PATH;
        $a_key_parts = self::fetchKeyParts(strtolower($key));
        foreach ($a_key_parts['file_dirs'] as $dir) {
            $path .= '/' . $dir;
        }
        $a_files_info = FileHelper::fileInfoByPathRecursive($path, $a_key_parts['file_start']);
        return self::transformFileInfo($a_files_info);
    }

    /**
     * Transverses the cache directory and lists all files found by the prefix.
     * Prefix is the first part(s) of a multipart key. e.g. one.two.three.it
     * could have the following prefixes, one, one.two,, one.two.three
     * @see self::filesInfoByPath() for more info
     *
     * @param string $prefix
     * @return array
     * @throws CacheException
     */
    public static function fileInfoByPrefix(string $prefix = ''): array
    {
        if (empty($prefix)) {
            throw new CacheException(
                'Prefix value was missing',
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        $path = CACHE_PATH . '/' . $prefix;
        return self::fileInfoByPath($path);
    }

    /**
     * Transverses the cache directory and lists all files found in the path.
     * This is the basis for self::filesInfoByPrefix and self::fileInfoByKey
     * Return array is an array of arrays that are
     * [
     *     'filename'      => $filename,
     *     'with_path'     => $path_and_file,
     *     'short_path'    => $short_path,
     *     'base_filename' => $base_filename,
     *     'expires'       => $expires,
     *     'file_ext'      => $extension
     * ]
     * @see \Ritc\Library\Helper\FileHelper() which this method uses
     *
     * @param string $path
     * @return array
     * @throws CacheException
     */
    public static function fileInfoByPath(string $path): array
    {
        if (empty($path)) {
            throw new CacheException(
                'Path value missing',
                ExceptionHelper::getCodeNumberCache('missing_value')
            );
        }
        $a_files = FileHelper::fileInfoByPathRecursive($path);
        return self::transformFileInfo($a_files);
    }

    /**
     * Returns an array with the start of the filename and an array of
     * the file paths it resides in.
     *
     * @param string $key
     * @return array
     */
    public static function fetchKeyParts(string $key): array
    {
        $a_parts = explode('.', strtolower($key));
        $stop_before = count($a_parts) - 1;
        $file_start = $a_parts[$stop_before];
        $a_file_dirs = [];
        for($i = 1; $i < $stop_before; $i++) {
            $a_file_dirs[] = $a_parts[$i - 1];
        }
        return ['file_start' => $file_start, 'file_dirs' => $a_file_dirs];
    }

    /**
     * Returns the file with path for the key and file extension
     * 
     * @param string $key
     * @param string $file_ext
     * @return string 
     * @throws CacheException
     */
    public static function fetchByKeyNewestPath(string $key, string $file_ext): string
    {
        try {
            $a_files = self::fileInfoByKey($key);
        }
        catch (CacheException $e) {
            throw new CacheException($e->getMessage(), $e->getCode());
        }
        if (empty($file_ext)) {
            $file_ext = 'txt';
        }
        if (count($a_files) > 1) {
            $expires_on = 0;
            $newer = -1;
            foreach ($a_files as $the_key => $a_file) {
                if (($a_file['file_ext'] === $file_ext) && ($a_file['expires'] > $expires_on)) {
                    $newer = $the_key;
                }
            }
            $file_w_path = $a_files[$newer]['with_path'];
        }
        else {
            $file_w_path = $a_files[0]['with_path'];
        }
        return $file_w_path; 
    }
    
    /**
     * Returns an array with information needed for cache files.
     *
     * @param array $a_files
     * @return array
     */
    public static function transformFileInfo(array $a_files): array
    {
        $a_return_values = [];
        foreach ($a_files as $a_file_info) {
            $filename = $a_file_info['filename'];
            $extension = $a_file_info['file_ext'];
            [$base_filename, $expires] = explode('.', $filename);
            try {
                $key = self::createKeyFromPath($a_file_info['file_w_path']);
            }
            catch (CacheException) {
                $key = '';
            }
            $a_return_values[] = [
                'key'           => $key,
                'filename'      => $filename,
                'with_path'     => $a_file_info['file_w_path'],
                'short_path'    => $a_file_info['path_in_cache'],
                'base_filename' => $base_filename,
                'expires'       => $expires,
                'file_ext'      => $extension
            ];
        }
        return $a_return_values;
    }
}