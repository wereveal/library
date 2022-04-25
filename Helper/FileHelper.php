<?php /** @noinspection PhpUndefinedMethodInspection */

namespace Ritc\Library\Helper;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class FileHelper
 * Basic File Helper
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @date    2022-03-15 15:17:25
 * @version 1.0.0-beta.1
 * @change_log
 * - v1.0.0-beta.1 - Initial version                            - 2022-03-15 wer
 */
class FileHelper
{
    /**
     * So I don't have to remember the finfo_file syntax.
     *
     * @param string $file_w_path
     * @param int    $flags
     * @return bool|string
     */
    public static function fileInfo(string $file_w_path, int $flags): bool|string
    {
        $flags = $flags ?? FILEINFO_MIME_TYPE;
        return finfo_file(finfo_open($flags), $file_w_path);
    }

    /**
     * Iterates recursively over a directory and returns a list of all the files.
     * The list is an array of arrays containing a lot of info regarding each
     * file.
     *
     * @param string $directory_path    Required
     * @param string $file_starts_with  Optional, if given, only shows files that match
     * @param string $file_ends_with    Optional, if given, only shows files that match
     * @return array
     */
    public static function fileInfoByPathRecursive(string $directory_path,
                                                   string $file_starts_with = '',
                                                   string $file_ends_with = ''
    ): array
    {
        $a_files = [];
        $o_files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory_path), RecursiveIteratorIterator::LEAVES_ONLY);
        $o_files->rewind();
        while ($o_files->valid()) {
            if (!$o_files->isDot()) {
                $filename = $o_files->getFileName();
                $file_ext = '.' . $o_files->getExtension();
                $go       = true;
                if (!empty($file_starts_with) && !str_starts_with($filename, $file_starts_with)) {
                    $go = false;
                }
                if (!empty($file_ends_with) && !str_ends_with($filename, $file_ends_with)) {
                    $go = false;
                }
                if ($go) {
                    $a_files[] = [
                        'filename'      => $filename,
                        'file_ext'      => $file_ext,
                        'filename_base' => $o_files->getBasename($file_ext),
                        'file_w_path'   => $o_files->getRealPath(),
                        'file_in_cache' => $o_files->getSubPathName(),
                        'path_in_cache' => $o_files->getSubPath(),
                        'file_type'     => $o_files->getType(),
                        'size'          => $o_files->getSize(),
                        'readable'      => $o_files->isReadable(),
                        'writable'      => $o_files->isWritable(),
                        'mime_type'     => finfo_file(
                            finfo_open(FILEINFO_MIME_TYPE),
                            $o_files->getRealPath()
                        )
                    ];
                }
            }
            $o_files->next();
        }
        return $a_files;
    }
}