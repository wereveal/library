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
     * Does all the stuff that is required to upload a file.
     * @param array $a_values required $a_values['final_file_name' => '', 'form_file_name' => '', 'file_path' => '', 'file_mime_type' => '']
     * @return bool
     */
    public static function uploadFile(array $a_values = [])
    {
        if (empty($a_values) || empty($a_values['final_file_name']) || empty($a_values['form_file_name']) || empty($a_values['file_path'])) {
            throw new \UnexpectedValueException('Required Values not provided.');
        }

        error_log(var_export($_FILES, true));
        $final_filename = $a_values['final_file_name'];
        $form_filename  = $a_values['form_file_name'];
        $file_path      = $a_values['file_path'];
        $file_mime_type = $a_values['file_mime_type'];

        if (!file_exists($file_path)) {
            throw new \UnexpectedValueException("File path invalid");
        }

        if (!is_uploaded_file($_FILES[$form_filename]['tmp_name'])) {
            throw new \UnexpectedValueException('The file was not found. Possible file upload attack.');
        }

        $r_finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($file_mime_type != '') {
            $found_file_mime_type = finfo_file($r_finfo, $_FILES[$form_filename]['tmp_name']);
            if ($file_mime_type !== $found_file_mime_type) {
                throw new \UnexpectedValueException("The file type specified doesn't match the file. Submitted: {$file_mime_type} vs found {$found_file_mime_type}");
            }
        }
        else {
            $file_mime_type = finfo_file($r_finfo, $_FILES[$form_filename]['tmp_name']);
            if (!self::isSortOfSafeExtension($file_mime_type)) {
                throw new \UnexpectedValueException("The file type may not be safe.");
            }
        }

        $error = self::getError($form_filename);
        if ($error != 'OK') {
            throw new \RuntimeException($error);
        }

        error_log($_FILES[$form_filename]['size'] . ' == ' . ini_get('upload_max_filesize'));
        $max_filesize = ini_get('upload_max_filesize');
        if (strpos($max_filesize, 'M') !== false) {
            $max_filesize = str_replace('M', '000000', $max_filesize);
        }
        elseif (strpos($max_filesize, 'K') !== false) {
            $max_filesize = str_replace('K', '000', $max_filesize);
        }
        error_log($_FILES[$form_filename]['size'] . ' == ' . $max_filesize);
        // May be redundant since $_FILES should have already spotted the error.
        if ($_FILES[$form_filename]['size'] >= $max_filesize) {
            throw new \RuntimeException("The uploaded file exceeds the max filesize: " . ini_get('upload_max_filesize'));
        }

        // make sure the file type is the one specified.
        if (!move_uploaded_file($_FILES[$form_filename]['tmp_name'], $file_path . '/' . $final_filename)) {
            throw new \RuntimeException("The file was not able to be uploaded.");
        }
        return true;
    }

    public static function getError($filename = '')
    {
        if ($filename == '') {
            return 'File name not suplied';
        }
        switch ($_FILES[$filename]['error']) {
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
}
