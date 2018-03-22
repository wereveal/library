<?php
/**
 * @brief     Helps with uploading files.
 * @ingroup   lib_helper
 * @file      Ritc/Library/Helper/UploadHelper.php
 * @namespace Ritc\Library\Helper
 * @author    William E Reveal <bill@revealitconsulting.com>
 * @version   1.0.0
 * @date      2017-11-10 11:12:26
 * @note Change Log
 * - v1.0.0 - Initial version        - 2017-11-10 wer
 */
namespace Ritc\Library\Helper;

/**
 * Class UploadHelper.
 * @class   UploadHelper
 * @package Ritc\Library\Helper
 */
class UploadHelper
{
    /**
     * Does all the stuff that is required to upload a single file.
     * @param array $a_values required $a_values = [
     *                        'tmp_name'        => '',
     *                        'error'           => '',
     *                        'size'            => '',
     *                        'type'            => '',
     *                        'real_type'       => '',
     *                        'final_file_name' => '',
     *                        'save_path'       => ''
     *                        ]
     * @param bool  $only_safe optional, defaults to true. Only allow safe, per self::isSortOfSafeExtension
     * @return bool
     */
    public static function uploadFile(array $a_values = [], $only_safe = true)
    {
        if (empty($a_values)) {
            throw new \UnexpectedValueException('Required Values not provided.');
        }

        if ($a_values['error'] != 'OK') {
            throw new \RuntimeException($a_values['error']);
        }

        if (!file_exists($a_values['save_path'])) {
            throw new \UnexpectedValueException("File path invalid");
        }

        if (!is_uploaded_file($a_values['tmp_name'])) {
            throw new \UnexpectedValueException('The file was not found. Possible file upload attack.');
        }

        // make sure the file type is safe and matches the files actual type.
        if ($only_safe) {
            $r_finfo = finfo_open(FILEINFO_MIME_TYPE);
            if (!empty($a_values['real_type'])) {
                if (!self::isSortOfSafeExtension($a_values['real_type'])) {
                    throw new \UnexpectedValueException("The file type may not be safe.");
                }
            }
            elseif (!empty($a_values['type'])) {
                $found_file_mime_type = finfo_file($r_finfo, $a_values['tmp_name']);
                if ($a_values['type'] !== $found_file_mime_type) {
                    throw new \UnexpectedValueException("The file type specified doesn't match the file. Submitted: {$a_values['type']} vs found {$found_file_mime_type}");
                }
            }
            else {
                $file_mime_type = finfo_file($r_finfo, $a_values['tmp_name']);
                if (!self::isSortOfSafeExtension($file_mime_type)) {
                    throw new \UnexpectedValueException("The file type may not be safe.");
                }
            }
        }

        // May be redundant since $_FILES should have already spotted the error.
        $max_filesize = ini_get('upload_max_filesize');
        if (strpos($max_filesize, 'M') !== false) {
            $max_filesize = str_replace('M', '000000', $max_filesize);
        }
        elseif (strpos($max_filesize, 'K') !== false) {
            $max_filesize = str_replace('K', '000', $max_filesize);
        }
        if ($a_values['size'] >= $max_filesize) {
            throw new \RuntimeException("The uploaded file exceeds the max filesize: " . ini_get('upload_max_filesize'));
        }

        $save_to = $a_values['save_path'] . '/' . $a_values['final_file_name'];
        if (!move_uploaded_file($a_values['tmp_name'], $save_to)) {
            throw new \RuntimeException("The file was not able to be uploaded.");
        }
        return true;
    }

    /**
     * Returns the $_FILES error reformatted to more human readable.
     * @param int $error
     * @return string
     */
    public static function getError($error = -1)
    {
        switch ($error) {
            case UPLOAD_ERR_OK:
                return 'OK';
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the max filesize: " . ini_get('upload_max_filesize');
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the max upload size: " . ini_get('post_max_size');
            case UPLOAD_ERR_PARTIAL:
                return 'The file was only partially uploaded.';
            case UPLOAD_ERR_NO_FILE:
                return 'The file was not uploaded.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'The setting for the temporary directory is invalid.';
            case UPLOAD_ERR_CANT_WRITE:
                return "Can't write to disk.";
            case UPLOAD_ERR_EXTENSION:
                return 'An extension stopped the file upload.';
            default:
                return 'Unknown error';
        }

    }

    /**
     * Looks to see if the extension is "semi-safe".
     * @param string $ext
     * @return bool
     */
    public static function isSortOfSafeExtension($ext = '')
    {
        switch ($ext) {
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
            case 'audio/mp3':
            case 'text/plain':
            case 'audio/wav':
            case 'image/tif':
            case 'image/tiff':
            case 'application/pdf':
                return true;
            default:
                return false;
        }
    }

    /**
     * Creates the values used for uploading a single file.
     * Can be $_FILES[form_field_name] or self::reorganizeFilesGlobal[first_one].
     * @param array  $a_upload_values required ['name','type','tmp_name','error','size']
     * @param string $file_path optional, defaults to '/files'
     * @param bool   $only_safe optional, defaults to true
     * @return array $a_values = [
     *                        'name'            => '',
     *                        'type'            => '',
     *                        'tmp_name'        => '',
     *                        'error'           => '',
     *                        'size'            => '',
     *                        'real_type'       => '',
     *                        'final_file_name' => '',
     *                        'save_path'       => ''
     *                        ]
     */
    public static function createUploadValues(array $a_upload_values = [], $file_path = '/files', $only_safe = true)
    {
        if (   empty($a_upload_values)
            || empty($a_upload_values['name'])
            || empty($a_upload_values['type'])
            || empty($a_upload_values['tmp_name'])
            || empty($a_upload_values['size'])
        ) {
            return ViewHelper::errorMessage('Required Value(s) not provided.');
        }
        $error = self::getError($a_upload_values['error']);
        if ($error === 'OK') {
            $uploaded_mime_type = MimeTypeHelper::getMimeFromFile($a_upload_values['tmp_name']);
            if ($only_safe) {
                if (!self::isSortOfSafeExtension($uploaded_mime_type)) {
                    return ViewHelper::errorMessage('The file type is not allowed.');
                }
            }
            $a_extensions = MimeTypeHelper::getExtensionFromMime($uploaded_mime_type);
            $real_ext     = empty($a_extensions[0]) ? 'txt' : $a_extensions[0];
            $org_filename = $a_upload_values['name'];
            $filename_ext = substr(strrchr($org_filename,'.'),1);
            if ($filename_ext != $real_ext) {
                $final_filename = trim(str_replace($filename_ext, '', $org_filename) . $real_ext);
            }
            else {
                $final_filename = $org_filename;
            }
            $a_upload_values['real_type']       = $uploaded_mime_type;
            $a_upload_values['final_file_name'] = $final_filename;
            $a_upload_values['save_path']       = SITE_PATH . $file_path;
            $a_upload_values['save_dir']        = $file_path;

        }
        else {
            return ViewHelper::errorMessage($error);
        }
        return $a_upload_values;
    }

    /**
     * Changes the $_FILES global array in instances where the form field has an array type name.
     * For example, fred[1], fred[2], barney[1], barney[2] in the form gets changed from<pre>
     * [
     *  fred => [
     *    name => [
     *      1 => '',
     *      2 => ''
     *    ],
     *    type => [
     *      1 => '',
     *      2 => ''
     *    ]
     *    etc,
     *   barney => [
     *      name => [[1 => [],2 => []]],
     *      type => [[1 => [],2 => []]],
     *      etc
     *   ]
     * to [
     *    fred => [
     *      1 => [
     *        name=>'',
     *        type=>'',
     *        etc
     *      ],
     *      2 => [
     *        name=>'',
     *        type=>'',
     *        etc
     *      ],
     *     barney => [
     *       1 => [],
     *       2 => [],
     *     ]
     * </pre>
     * @return array
     */
    public static function reorganizeFilesGlobal()
    {
        $a_reorg_files = [];
        foreach ($_FILES as $field_name => $a_values) {
            if (is_array($a_values['tmp_name'])) {
                $keys = [];
                foreach ($a_values['tmp_name'] as $key => $value) {
                    $keys[] = $key;
                }
                foreach ($keys as $value) {
                    $a_reorg_files[$field_name][$value]['name']     = $a_values['name'][$value];
                    $a_reorg_files[$field_name][$value]['type']     = $a_values['type'][$value];
                    $a_reorg_files[$field_name][$value]['tmp_name'] = $a_values['tmp_name'][$value];
                    $a_reorg_files[$field_name][$value]['error']    = $a_values['error'][$value];
                    $a_reorg_files[$field_name][$value]['size']     = $a_values['size'][$value];
                }
            }
            else {
                $a_reorg_files[$field_name] = $a_values;
            }
        }
        return $a_reorg_files;
    }
}
