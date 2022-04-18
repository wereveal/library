<?php
namespace Ritc\Library\Interfaces;

use Ritc\Library\Exceptions\CacheException;

/**
 * Interface CacheHelperInterface
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @date    2022-03-15 15:17:25
 * @version 1.0.0-beta.1
 * @change_log
 * - v1.0.0-beta.1 - Initial version                            - 2022-03-15 wer
 */
interface CacheHelperInterface
{
    /**
     * Creates a cache key based on the path of a file.
     * Not sure when this may be needed but seems to be so.
     *
     * @param string $file_w_path
     * @return string
     * @throws CacheException
     */
    public static function createKeyFromPath(string $file_w_path): string;

    /**
     * Returns the path(s) inside the cache directory for a specific key.
     *
     * This is based on the philosophy of that the cache key can be multi-part
     * based on dots, e.g. first.second.third The key is turned into a file with
     * path, expirations, and file extension that looks like
     * /cache/first/second/third.expires.ext
     *
     * @param string $key Required
     * @return array      Makes an assumption that multiple files may exist for a given key.
     * @throws CacheException
     */
    public static function fetchFilePathsFromKey(string $key): array;

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
     * @param string $key Required
     * @return array
     * @throws CacheException;
     * @see self::filesInfoByPath()
     */
    public static function fileInfoByKey(string $key): array;

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
     * @param string $path Required. Full path to the directory to transvers.
     * @return array
     * @throws CacheException
     */
    public static function fileInfoByPath(string $path): array;

    /**
     * Transverses the cache directory and lists all files found by the prefix.
     * Prefix is the first part(s) of a multipart key. e.g. one.two.three.it
     * could have the following prefixes, one, one.two,, one.two.three
     *
     * @param string $prefix
     * @return array
     * @throws CacheException;
     * @see self::filesInfoByPath() for more info
     */
    public static function fileInfoByPrefix(string $prefix): array;
}