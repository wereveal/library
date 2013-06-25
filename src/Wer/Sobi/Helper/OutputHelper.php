<?php

namespace Wer\Sobi\Helper;

class OutputHelper
{
	/*
	 * Returns a message variables needed for a twig template.
	 * @param string $message the message to be formated
	 * @param string $type type of message: success, info, warning, failure
	 * @param string $alt_text optional, if given will replace the default
	 *    text for the type of message.
	**/
	public function messageProperties( $message = '', $type = 'info', $alt_text = '' )
	{
		$images_dir = 'images';
		if ( $message == '' ) {
			$type = '';
		}
		switch ( $type ) {
			case 'success':
				$alt_text = $alt_text != '' ? $alt_text : 'Success!';
				$image_src = $images_dir . '/success.png';
				$class = 'msg-success';
				break;
			case 'info':
				$alt_text = $alt_text != '' ? $alt_text : 'Information';
				$image_src = $images_dir . '/info.png';
				$class = 'msg-info';
				break;
			case 'warning':
				$alt_text = $alt_text != '' ? $alt_text : 'Warning!';
				$image_src = $images_dir . '/warning.png';
				$class = 'msg-warning';
				break;
			case 'failure':
				$alt_text = $alt_text != '' ? $alt_text : 'Failure!';
				$image_src = $images_dir . '/failure.png';
				$class = 'msg-failure';
				break;
			case 'code':
				$alt_text = $alt_text != '' ? $alt_text : 'Code';
				$image_src = $images_dir . '/info.png';
				$class = 'msg-code';
				break;
			default:
				$alt_text = '';
				$class = '';
				$image_src = '';
		}

		return array( 'message'=>$message, 'alt_text'=>$alt_text, 'image_src'=>$image_src, 'class'=>$class );
	}
}
