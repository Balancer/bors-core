<?php

require_once 'Image/Transform.php';

function image_file_scale($file_in, &$file_out, $width, $height, $opts)
{
	$img =& Image_Transform::factory(config('image_transform_engine'));
	
	if(PEAR::isError($img))
		return $img;

	if(!$width)
		$width = $height * 100 + 64;
	if(!$height)
		$height = $width * 100 + 64;

	$img->load($file_in);
	if(!$opts)
		$img->fit($width, $height);
	else
	{
		$opts = explode(',', $opts);
		
		$img_h = $img->getImageHeight();
		$img_w = $img->getImageWidth();
	
		$up = in_array('up', $opts);
		$crop = in_array('crop', $opts);
		
		if($up || ($img_h >= $height && $img_w >= $width)) // ресайз обязателен
		{
			$upw = $img_w*$height/$img_h;
			if($upw > $width)
				$uph = $height;
			else
			{
				$upw = $width;
				$uph = $img_h*$width/$img_w;
			}
			
//			bors_exit("img={$img_w}x{$img_h}, need={$width}x{$height}, up=$up, crop=$crop, upwh={$upw}x{$uph}");
			$img->resize($upw, $uph);
			if($upw > $width || $uph > $height)
				$img->crop($width, $height, ($upw-$width)/2, ($uph-$height)/2);
		}
		
	}
	
	mkpath(dirname($file_out), 0777, true);
	$img->save($file_out, $img->getImageType());
	chmod($file_out, 0664);
	return $img->isError();
}
