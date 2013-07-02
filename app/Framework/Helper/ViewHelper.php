<?php

namespace Wer\Framework\Helper;

class ViewHelper
{
    /**
	 *  Returns a message variables needed for a twig template.
	 *  @param array $a_message_params array(
	 *      'message'       => '',         // obviously if no message this is stupid
	 *      'type'          => 'info',     // info is the generic message type
	 *	    'message_class' => '',         // defaults to a class based on type
	 *	    'image_src'     => '',         // is the web path including image file name
	 *	    'image_class'   => 'msg-icon', // usually used to float image/manage where it goes
	 *	    'alt_text'      => '',         // for the image
	 *	    'other_stuph'   => ''          // a just in case thing.
	 *	)
	 *  @return array values for the template.
	**/
	public function messageProperties($a_message_params = array())
	{
	    $alt_text    = '';
	    $class       = '';
		$image_dir   = '/assets/images/icons';
		$image_class = 'msg-icon';
		$image_src   = '';
		$message     = '';
		$msg_class   = '';
		$other_stuph = '';
		$type        = 'info';
	    foreach ($a_message_params as $key => $value) {
	        switch ($key) {
	            case 'type':
	                $type = $value;
	                break;
	            case 'message':
	                $message = $value;
	                break;
	            case 'message_class':
	                $msg_class = $value;
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
	            case 'other_stuph':
	                $other_stuph = $value;
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
				$class     = $msg_class != '' ? $msg_class : 'msg-success';
				break;
			case 'info':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Information';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/info.png';
				$class     = $msg_class != '' ? $msg_class : 'msg-info';
				break;
			case 'warning':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Warning!';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/warning.png';
				$class     = $msg_class != '' ? $msg_class : 'msg-warning';
				break;
			case 'failure':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Failure!';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/failure.png';
				$class     = $msg_class != '' ? $msg_class : 'msg-failure';
				break;
			case 'code':
				$alt_text  = $alt_text  != '' ? $alt_text  : 'Code';
				$image_src = $image_src != '' ? $image_src : $image_dir . '/info.png';
				$class     = $msg_class != '' ? $msg_class : 'msg-code';
				break;
			default:
			    $class       = '';
			    $image_src   = '';
			    $image_class = '';
			    $message     = '';
			    $alt_text    = '';
			    $other_stuph = '';
		}
		return array(
		    'message'       => $message,
		    'message_class' => $class,
		    'image_src'     => $image_src,
		    'image_class'   => $image_class,
		    'alt_text'      => $alt_text,
		    'other_stuph'   => $other_stuph
		);
	}
}
