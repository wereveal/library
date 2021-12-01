<?php
/**
 * Class ViewHelper
 * @package Ritc_Library
 */
namespace Ritc\Library\Helper;

/**
 * Various helper functions for views.
 *
 * @author  William E Reveal <bill@revealitconsulting.com>
 * @version 3.0.0
 * @date    2021-11-29 17:03:38
 * @change_log
 * - v3.0.0 - updated to php8                   - 2021-11-29 wer
 * - v2.1.0 - Added addMessage method           - 2018-05-16 wer
 * - v2.0.0 - Renamed 'the' method              - 2017-06-20 wer
 * - v1.1.1 - little sensitization of values    - 11/05/2015 wer
 * - v1.1.0 - added lazy man's methods          - 09/25/2015 wer
 * - v1.0.1 - changed function to static        - 09/25/2014 wer
 * - v1.0.1 - minor key name change             - 12/31/2013 wer
 * - v1.0.0 - initial file                      - 07/30/2013 wer
 */
class ViewHelper
{
    /**
     * Adds a message string to a message array.
     *
     * @param array  $a_message    Advised but optional. If empty creates a new message array.
     * @param string $message      Required, nothing to add otherwise.
     * @param string $message_type Optional, defaults to message type of original message, or info if none provided.
     * @return array
     */
    public static function addMessage(array $a_message = [], string $message = '', string $message_type = ''):array
    {
        if (empty($message)) {
            return $a_message;
        }
        $message_type = empty($message_type)
            ? $a_message['type']
            : $message_type
        ;
        $new_message = empty($a_message['message'])
            ? $message
            : $a_message['message'] . '<br>' . $message;
        return match ($message_type) {
            'success' => self::successMessage($new_message),
            'warning' => self::warningMessage($new_message),
            'error'   => self::errorMessage($new_message),
            'failure' => self::failureMessage($new_message),
            'code'    => self::codeMessage($new_message),
            default   => self::infoMessage($new_message),
        };
    }

    /**
	 * Returns a message variables needed for a twig template.
     *
	 * @param array $a_message_params <pre>array(
	 *     'message'       => '',              // obviously if no message this is stupid
	 *     'type'          => 'info',          // info is the generic message type
	 *	    'message_class' => '',             // defaults to a class based on type
	 *	    'image_src'     => '',             // is the web path including image file name
	 *	    'image_class'   => 'message icon', // usually used to float image/manage where it goes
	 *	    'alt_text'      => '',             // for the image
	 *	    'extras'        => ''              // a just in case thing.
	 *	)</pre>
	 * @return array values for the template.
	 */
	public static function fullMessage(array $a_message_params = array()):array
    {
	    if (empty($a_message_params)) {
	        $a_message_params = ['type' => '', 'message' => ''];
	    }
	    $alt_text    = '';
		$image_dir   = IMAGES_DIR . '/icons';
		$image_class = 'icon';
		$image_src   = '';
		$message     = '';
		$msg_class   = '';
		$extras      = '';
		$type        = 'info';
	    foreach ($a_message_params as $key => $value) {
	        switch ($key) {
	            case 'type':
	                $type = $value;
	                break;
	            case 'message':
	                $message = $value;
	                break;
                case 'msg_class':
	            case 'message_class':
	                $msg_class = $value;
                    break;
	            case 'alt_text':
	                $alt_text = $value;
	                break;
	            case 'image_class':
	                $image_class = $value;
	                break;
	            case 'image_dir':
	                $image_dir = $value;
	                break;
	            case 'image_src':
	                $image_src = $value;
	                break;
	            case 'extras':
	                $extras = $value;
	            default:
	                // do nothing
	        }
	    }
		if ($message === '') {
			$type = '';
		}
		switch ($type) {
			case 'success':
				$alt_text  = $alt_text  !== '' ? $alt_text  : 'Success!';
				$image_src = $image_src !== '' ? $image_src : $image_dir . '/success.png';
				$msg_class = $msg_class !== '' ? $msg_class : 'message success';
				break;
			case 'info':
				$alt_text  = $alt_text  !== '' ? $alt_text  : 'Information';
				$image_src = $image_src !== '' ? $image_src : $image_dir . '/information.png';
                $msg_class = $msg_class !== '' ? $msg_class : 'message info';
				break;
			case 'warning':
				$alt_text  = $alt_text  !== '' ? $alt_text  : 'Warning!';
				$image_src = $image_src !== '' ? $image_src : $image_dir . '/warning.png';
                $msg_class = $msg_class !== '' ? $msg_class : 'message warning';
				break;
			case 'error':
				$alt_text  = $alt_text  !== '' ? $alt_text  : 'Error!';
				$image_src = $image_src !== '' ? $image_src : $image_dir . '/error.png';
                $msg_class = $msg_class !== '' ? $msg_class : 'message error';
				break;
			case 'failure':
				$alt_text  = $alt_text  !== '' ? $alt_text  : 'Failure!';
				$image_src = $image_src !== '' ? $image_src : $image_dir . '/failure.png';
                $msg_class = $msg_class !== '' ? $msg_class : 'message failure';
				break;
			case 'code':
				$alt_text  = $alt_text  !== '' ? $alt_text  : 'Code';
				$image_src = $image_src !== '' ? $image_src : $image_dir . '/code.png';
                $msg_class = $msg_class !== '' ? $msg_class : 'message code';
                if (!str_contains($message, '<pre>')) {
                    $message = '<pre>' . $message . '</pre>';
                }
				break;
			default:
                $msg_class   = '';
			    $image_src   = '';
			    $image_class = '';
			    $message     = '';
			    $alt_text    = '';
			    $extras      = '';
		}
		return array(
		    'message'       => $message,
		    'message_class' => $msg_class,
		    'image_src'     => $image_src,
		    'image_class'   => $image_class,
		    'alt_text'      => $alt_text,
		    'extras'        => $extras
		);
	}

    /**
     * Lazy man's way of creating array used by self::fullMessage method.
     *
     * @param string $message
     * @return array
     */
    public static function codeMessage(string $message = ''):array
    {
        if ($message === '') {
            $message = 'Insert code here.';
        }
        return [
            'message' => $message,
            'type'    => 'code'
        ];
    }

    /**
     * Lazy man's way of creating array used by self::fullMessage method.
     *
     * @param string $message
     * @return array
     */
    public static function errorMessage(string $message = ''):array
    {
        if ($message === '') {
            $message = 'An Error Has Occured. Please Try Again.';
        }
        return [
            'message' => $message,
            'type'    => 'error'
        ];
    }

	/**
	 * Lazy man's way of creating array used by self::fullMessage method.
	 *
	 * @param string $message
	 * @return array
	 */
	public static function failureMessage(string $message = ''):array
    {
		if ($message === '') {
			$message = 'A Problem Has Occured. Please Try Again.';
		}
		return [
			'message' => $message,
			'type'    => 'failure'
		];
	}

    /**
     * Lazy man's way of creating array used by self::fullMessage method.
     *
     * @param string $message
     * @return array
     */
    public static function infoMessage(string $message = ''):array
    {
        if ($message === '') {
            $message = 'Insert helpful info here.';
        }
        return [
            'message' => $message,
            'type'    => 'info'
        ];
    }

	/**
	 * Lazy man's way of creating array used by self::fullMessage method.
	 *
	 * @param string $message
	 * @return array
	 */
	public static function successMessage(string $message = ''):array
    {
		if ($message === '') {
			$message = 'Success!';
		}
		return [
			'message' => $message,
			'type'    => 'success'
		];
	}

    /**
     * Lazy man's way of creating array used by self::fullMessage method.
     *
     * @param string $message
     * @return array
     */
    public static function warningMessage(string $message = ''):array
    {
        if ($message === '') {
            $message = 'A Problem Has Occured. Please Try Again.';
        }
        return [
            'message' => $message,
            'type'    => 'warning'
        ];
    }
}
