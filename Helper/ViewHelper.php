<?php
/**
 *  @brief Various helper functions for views.
 *  @file ViewHelper.php
 *  @namespace Ritc/Library/Helper
 *  @class ViewHelper
 *  @author William E Reveal <bill@revealitconsulting.com>
 *  @version 1.0.2
 *  @date 2014-09-25 10:30:54
 *  @note Change Log
 *      v1.0.1 - changed function to static - 09/25/2014 wer
 *      v1.0.1 - minor key name change - 12/31/2013 wer
 *      v1.0.0 - intial file - 07/30/2013 wer
 *  @note RITC Library
 *  @ingroup ritc_library helper
**/
namespace Ritc\Library\Helper;

class ViewHelper
{
    /**
	 *  Returns a message variables needed for a twig template.
	 *  @param array $a_message_params array(
	 *      'message'       => '',         // obviously if no message this is stupid
	 *      'type'          => 'info',     // info is the generic message type
	 *	    'message_class' => '',         // defaults to a class based on type
	 *	    'image_src'     => '',         // is the web path including image file name
	 *	    'image_class'   => 'message icon', // usually used to float image/manage where it goes
	 *	    'alt_text'      => '',         // for the image
	 *	    'extras'        => ''          // a just in case thing.
	 *	)
	 *  @return array values for the template.
	**/
	public static function messageProperties($a_message_params = array())
	{
	    $alt_text    = '';
		$image_dir   = IMAGE_DIR . '/icons';
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
		if ($message == '') {
			$type = '';
		}
		switch ($type) {
			case 'success':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Success!';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/success.png';
				$msg_class = $msg_class != '' ? $msg_class : 'message success';
				break;
			case 'info':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Information';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/info.png';
                $msg_class = $msg_class != '' ? $msg_class : 'message info';
				break;
			case 'warning':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Warning!';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/warning.png';
                $msg_class = $msg_class != '' ? $msg_class : 'message warning';
				break;
			case 'error':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Error!';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/error.png';
                $msg_class = $msg_class != '' ? $msg_class : 'message error';
				break;
			case 'failure':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Failure!';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/failure.png';
                $msg_class = $msg_class != '' ? $msg_class : 'message failure';
				break;
			case 'code':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Code';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/info.png';
                $msg_class = $msg_class != '' ? $msg_class : 'message code';
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
}
