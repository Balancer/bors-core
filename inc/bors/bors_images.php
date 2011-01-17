<?php

function bors_image_message($message, $params = array())
{
	$ww = intval(defval($params, 'width', 100));
	$hh = intval(defval($params, 'height', 100));

	$img = imagecreatetruecolor($ww, $hh);

	$font = '/usr/share/fonts/corefonts/verdana.ttf';
	$red   = imagecolorallocate($img, 255,   0,   0);
	$black = imagecolorallocate($img,   0,   0,   0);
	$white = imagecolorallocate($img, 255, 255, 255);
	$gray  = imagecolorallocate($img, 128, 128, 128);
		
//	$transparent = imagecolorallocate($img, 255,99,140);
//  imagecolortransparent($img, $transparent);
//	imagefill($img, 0, 0, $transparent);

	imagefill($img, 0, 0, $gray);
		

	$x = 4;
	$y = $hh/2;
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
