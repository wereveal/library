<?php
/**
 * Class UploadHelper
 * @package RITC_Library
 */
namespace Ritc\Library\Helper;

/**
 * Helps with uploading files.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version v2.1.0
 * @date    2018-03-29 14:13:03
 * ## Change Log
 * - v2.1.0 - added new methods to handle file types not    - 2018-03-29 wer
 *            specified in the sort of safe method. Changed
 *            default check for safe file types to new
 *            method, depreciating sortOfSafe method.
 * - v2.0.0 - major rewrite to accommodate multiple files   - 2018-03-23 wer
 *            Backwards compatibility questionable.
 * - v1.0.0 - Initial version                               - 2017-11-10 wer
 */
class UploadHelper
{
    /** @var array  */
    private static $a_allowed_file_types = [];

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
     * @param bool  $only_safe optional, defaults to true. Only allow safe, per self::isAllowedFileType
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
                if (!self::isAllowedFileType($a_values['real_type'])) {
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
                if (!self::isAllowedFileType($file_mime_type)) {
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
     * Checks to see if the file type is allowed for uploading.
     * @param string $file_type
     * @return bool
     */
    public static function isAllowedFileType($file_type = '')
    {
        if (empty(self::$a_allowed_file_types)) {
            self::setAllowedFileTypes();
        }
        if (in_array($file_type, self::$a_allowed_file_types)) {
            return true;
        }
        return false;
    }

    /**
     * Looks to see if the extension is "semi-safe".
     * @param string $file_type
     * @return bool
     */
    public static function isSortOfSafeFileType($file_type = '')
    {
        switch ($file_type) {
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
                if (self::isAllowedFileType($uploaded_mime_type)) {
                    if (false) {
                        return ViewHelper::errorMessage('The file type is not allowed.');
                    }
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
            $a_upload_values['save_path']       = PUBLIC_PATH . $file_path;
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

    /**
     * Adds an additional extension to the class property a_allowed_file_types.
     * @param string $value
     */
    public static function addAllowedFileType($value = '')
    {
        if (!empty($value)) {
            if (!isset(self::$a_allowed_file_types['value'])) {
                self::$a_allowed_file_types[] = $value;
            }
        }
    }

    /**
     * Standard setter for class property a_allowed_file_types.
     * @param array $a_values
     */
    public static function setAllowedFileTypes(array $a_values = [])
    {
        if (empty($a_values)) {
            $a_values = [
                'image/jpg',
                'image/jpg',
                'image/jpeg',
                'image/png',
                'image/gif',
                'audio/mp3',
                'text/plain',
                'audio/wav',
                'image/tif',
                'image/tiff',
                'application/pdf'
            ];
        }
        self::$a_allowed_file_types = $a_values;
    }

    /**
     * Standard getter for class property a_allowed_file_types;
     * @return array
     */
    public static function getAllowedFileTypes()
    {
        return self::$a_allowed_file_types;
    }
}
