<?php

function bors_image_message($message, $params = array())
{
	debug_hidden_log('image-messages', $message);

	$ww = intval(defval($params, 'width', 640));
	$hh = intval(defval($params, 'height', 400));

	$img = imagecreatetruecolor($ww, $hh);

	$font = BORS_3RD_PARTY.'/fonts/verdana.ttf';
	$red   = imagecolorallocate($img, 255,   0,   0);
	$black = imagecolorallocate($img,   0,   0,   0);
	$white = imagecolorallocate($img, 255, 255, 255);
	$gray  = imagecolorallocate($img, 128, 128, 128);

//	$transparent = imagecolorallocate($img, 255,99,140);
//  imagecolortransparent($img, $transparent);
//	imagefill($img, 0, 0, $transparent);

	imagefill($img, 0, 0, $gray);

	$x = 4;
	$y = 20;
	$angle = 0;
	$size = 7;

	$color = defval($params, 'color', 'black');

	imagettftext($img, $size, $angle, $x, $y, $$color, $font, $message);

	ob_start();
	imagegif($img);
	$result = ob_get_contents();
	ob_end_clean();

	imagedestroy($img);

	if(defval($params, 'print', false))
	{
		@header('Content-type: image/gif');
		echo $result;
	}
	return $result;
}
