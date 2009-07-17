<?php

require_once 'Image/Transform.php';
require_once 'inc/filesystem.php';
require_once 'inc/processes.php';
require_once 'inc/debug.php';

function image_file_scale($file_in, &$file_out, $width, $height, $opts = '')
{
	while(!bors_thread_lock('image_file_scale', 30, "{$file_in} => {$file_out} [{$width}x{$height}($opts)]"))
		usleep(rand(1000, 5000));

	if(file_exists($file_out))
	{
		config_set('bors-image-lasterror', ec('Файл не существует'));
		bors_thread_unlock('image_file_scale');
		return false;
	}

	if(config('pics_base_safemodded'))
	{
		$file_in = str_replace(config('pics_base_dir'), config('pics_base_url'), $file_in);
	}

	$data = getimagesize($file_in);

	if(!$data || !$data[0])
	{
		config_set('bors-image-lasterror', ec('Не могу определить размер файла'));
		debug_hidden_log('image-error', "Can't get width for image {$file_in} (tr resize to {$file_out}($width, $height, $opts); WxH = ".@$data[0].'x'.@$data[1]);
		bors_thread_unlock('image_file_scale');
		return false;
	}

	if(($data[0] > config('images_resize_max_width')
		|| $data[1] > config('images_resize_max_height')
		|| $data[0]*$data[1] > config('images_resize_max_area')
	) && (filesize($file_in) > config('images_resize_filesize_enabled')))
	{
		config_set('bors-image-lasterror', ec('Слишком большой (').($data[0].'x'.$data[1].'='.sprintf('%.1f',$data[0]*$data[1]/1024/1024)).ec("Мпкс) файл.
Предел для генерации превью ").config('images_resize_max_width')."x".config('images_resize_max_height').ec("
или ").sprintf('%.1f',config('images_resize_max_area')/1024/1024).ec('Мпкс'));
		debug_hidden_log('image-error', "Image {$file_in} too big to resize to 
{$file_out}
geo = ($width, $height, $opts)
Source WxH= ".$data[0].'x'.$data[1].'='.($data[0]*$data[1])."
Max=".config('images_resize_max_width')."x".config('images_resize_max_height')."=".config('images_resize_max_area')
);
		bors_thread_unlock('image_file_scale');
		return false;
	}

//	echo "image_file_scale($file_in, $file_out, $width, $height, $opts)<br/>\n"; exit();

	$img =& Image_Transform::factory(config('image_transform_engine'));

	if(PEAR::isError($img))
	{
		bors_thread_unlock('image_file_scale');
		return $img;
	}

	$img->load($file_in);
	if(!$opts)
	{
		if(!$width)
			$width = $height * 100 + 64;
		if(!$height)
			$height = $width * 100 + 64;

		$img->fit($width, $height);
	}
	else
	{
		$opts = explode(',', $opts);

		$img_h = $img->getImageHeight();
		$img_w = $img->getImageWidth();

		if(!$width)
			$width = $height * $img_w / $img_h;
		if(!$height)
			$height = $width * $img_h / $img_w;

		$scale_up = in_array('up', $opts);
		$crop = in_array('crop', $opts);

		$scale_down = ($height && $img_h >= $height) || ($width && $img_w >= $width);

		if($scale_up || $scale_down) // ресайз обязателен
		{
			$upw = $img_w*$height/$img_h;
			if($upw > $width)
				$uph = $height;
			else
			{
				$upw = $width;
				$uph = $img_h*$width/$img_w;
			}

			$img->resize($upw, $uph);
			if($upw > $width || $uph > $height)
				$img->crop($width, $height, ($upw-$width)/2, ($uph-$height)/2);

//			bors_exit("img={$img_w}x{$img_h}, need={$width}x{$height}, up=$scale_up, crop=$crop, upWxH={$upw}x{$uph}");
		}

	}

	mkpath(dirname($file_out), 0777);
	$img->save($file_out, $img->getImageType());
	@chmod($file_out, 0664);
	bors_thread_unlock('image_file_scale');
	return $img->isError();
}
