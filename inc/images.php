<?php

function image_file_scale($file_in, $file_out, $width, $height, $opts = NULL)
{
	if(file_exists($file_out))
		return false;

	if(!file_exists($file_in))
	{
		config_set('bors-image-lasterror', "[10] Source image file not exists:\n".$file_in);
		return false;
	}

	require_once 'composer/vendor/autoload.php';
	// http://intervention.olivervogel.net/image

	try
	{
		$img = Intervention\Image\Image::make($file_in);
	}
	catch(Exception $e)
	{
		bors_debug::syslog('image-scale-exception', blib_exception::factory($e));
		config_set('bors-image-lasterror', "[22] Thumbnail make exception\nImage:\n".$file_in."\nException:\n".$e->getMessage());
		return false;
	}

	if($width && !$height)
	{
		if($width < $img->width)
			$img->widen($width);
	}
	elseif(!$width && $height)
	{
		if($height < $img->height)
			$img->heighten($height);
	}
	elseif(!$opts)
		$img->resize($width, $height, true, false);
	elseif($opts == 'up,crop')
		$img->grab($width, $height); // Пропорции + обрезка + увеличение, если надо
	elseif($opts == 'crop')
	{
		// Большие картинки уменьшаем и кропаем, маленькие оставляем как есть.
		if($height < $img->height || $width < $img->width)
			$img->grab($width, $height); // Пропорции + обрезка + увеличение, если надо
		// else // Вся картинка меньше, ничего не делаем.
//		var_dump($img->height, $img->width); exit();
	}
	else
	{
		bors_debug::syslog('00-image-options-code-append', "Unknown options in image_file_scale($file_in, $file_out, $width, $height, $opts)");
		$img->grab($width, $height); // Пропорции + обрезка + увеличение, если надо
	}

	$img->save($file_out, 85);
	return false;

	$data = getimagesize($file_in);

	if(!$data || !$data[0])
	{
		config_set('bors-image-lasterror', ec('Не могу определить размеры изображения'));
		debug_hidden_log('image-error', "Can't get width for image {$file_in}\n(tr resize to {$file_out}($width, $height, $opts);\nWxH = ".@$data[0].'x'.@$data[1]);
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
		bors_image_resize_error_return(config('bors-image-lasterror'), $file_out, $width, $height);

		return false;
	}

	$img = Image_Transform::factory(config('image_transform_engine'));

	// Маскируем E_STRICT на старых PEAR_Image_Transform
	$old_de = ini_set('display_errors', '0');
	$old_er = error_reporting(E_ALL & ~E_STRICT);

	$pear_err = PEAR::isError($img);

	ini_set('display_errors', $old_de);
	error_reporting($old_er);

	if($pear_err)
	{
		config_set('bors-image-lasterror', ec("Ошибка PEAR:\n").$img->getMessage());
		return false;
	}

	$img->load($file_in);
	mkpath(dirname($file_out), 0777);

	if(!$opts)
	{
		if(!$width)
			$width = $height * 100 + 64;

		if(!$height)
			$height = $width * 100 + 64;

		// Хак:
		if(config('image_transform_engine') == 'Imagick3')
		{
			$imagick = $img->imagick;
			$format = $imagick->getImageFormat();
			if($format == 'GIF')
			{
				$origin_h = $img->getImageHeight();
				$origin_w = $img->getImageWidth();
				if($origin_h < $height && $origin_w < $width)
				{
					$width  = $origin_w;
					$height = $origin_h;
				}

				$imagick = $imagick->coalesceImages();
				do
				{
					// Так — нормально, вписывается в габариты
					$imagick->resizeImage($width, $height, Imagick::FILTER_BOX, 1, true);
					//	Полное изменение, теряются пропорции
//					$imagick->resizeImage($width, $height, Imagick::FILTER_BOX, 1);
					// Получается огромная хрень
//					$imagick->resampleImage($width, $height, Imagick::FILTER_BOX, 1);
				} while ($imagick->nextImage());
				$imagick = $imagick->deconstructImages();
				mkpath($d = dirname($file_out), 0777);
				if(is_dir($d) && is_writable($d))
				{
					$imagick->writeImages($file_out, true);
					chmod($file_out, 0666);
				}
				return $img->isError();
			}
		}

		$img->fit($width, $height);
//		if(config('is_developer')) { var_dump($img); exit(); }
	}
	else
	{
		$opts = explode(',', $opts);
//		if(config('is_developer')) { var_dump($opts); exit(); }

		$img_h = $img->getImageHeight();
		$img_w = $img->getImageWidth();

		if(!$img_h || !$img_w)
		{
			bors_debug::syslog('image-error', "Zero size {$img_w}x{$img_h} of {$file_in} => {$file_out} {$width}x{$height}");
			return false;
		}

		if(!$width)
			$width = $height * $img_w / $img_h;

		if(!$height)
			$height = $width * $img_h / $img_w;

		$scale_up = in_array('up', $opts);
		$crop = in_array('crop', $opts);
		$fillpad = in_array('fillpad', $opts);

		$scale_down = ($height && $img_h >= $height) || ($width && $img_w >= $width);

//		if(config('is_developer')) { var_dump('scale', $scale_up, $scale_down); exit(); }

		if($scale_up || $scale_down) // ресайз обязателен
		{
			// Если заполняем картинку, то до полного размера
			if($fillpad)
			{
				$upw = $width;
				$uph = $height;
			}
			else
			{
				$upw = $img_w*$height/$img_h;
				if($upw > $width)
				{
					if($crop)
					{
						$uph = $height;
					}
					else
					{
						$upw = $width;
						$uph = $img_h*$width/$img_w;
					}
				}
				else
				{
					$uph = $img_h*$width/$img_w;
					if($crop)
					{
						$upw = $width;
					}
					else
					{
						if($uph > $height)
						{
							$uph = $height;
							$upw = $img_w*$height/$img_h;
						}
					}
				}
			}

//			if(config('is_developer')) { var_dump('size', array( 'test_up_w' => $img_w*$height/$img_h, 'test_up_h' => $img_h*$width/$img_w, 'width' => $width, 'height' => $height, 'upw' => $upw, 'uph' => $uph)); exit('stop'); }

			if($fillpad)
				$img->fit(round(0.95*$upw), round(0.95*$uph));
			else
				$img->resize($upw, $uph);

			$given_w = $img->getNewImageWidth();
			$given_h = $img->getNewImageHeight();

			if($fillpad && ($given_w < $upw || $given_h < $uph))
			{
				//TODO: Жёсткий хардкод GD1. Даже не представляю, как менять на нативный вариант
				$new_img = ImageCreate($upw, $uph);
				ImageCopyResized($new_img, $img->imageHandle, round(($upw - $given_w)/2), round(($uph - $given_h)/2), 0, 0, $given_w, $given_h, $given_w, $given_h);
				$img->old_image = $img->imageHandle;
				$img->imageHandle = $new_img;
				$img->resized = true;
				$img->new_x = $upw;
				$img->new_y = $uph;
			}
			elseif($upw > $width || $uph > $height)
				$img->crop($width, $height, ($upw-$width)/2, ($uph-$height)/2);

//			bors_exit("img={$img_w}x{$img_h}, need={$width}x{$height}, up=$scale_up, crop=$crop, upWxH={$upw}x{$uph}");
		}

	}

	//TODO: выкинуть нафиг Image_Transform, а то приходится маскировать E_STRICT
	@$img->save($file_out, $img->getImageType());

	@chmod($file_out, 0666);
	return $img->isError();
}

function bors_image_resize_error_return($message, $file_out, $width, $height)
{
	require_once('inc/bors/bors_images.php');
	$image = bors_image_message(ec("Ошибка изменения размера изображения:\n").$message, array(
		'width' => $width ? $width : 300,
		'height' => $height ? $height: 100,
	));
	mkpath(dirname($file_out), 0777);
	file_put_contents($file_out, $image);
	@chmod($file_out, 0666);
	return false;
}

function bors_icon($image, $params = array())
{
	$title = defval($params, 'title');
	$alt   = defval($params, 'alt', '[IMG]');

	if(!preg_match('/\.(png|gif)$/', $image))
	{
		foreach(array(
				BORS_CORE.'/shared/i16' => '/_bors/i16',
				BORS_EXT.'/htdocs/_bors-ext/i16' => '/_bors-ext/i16'
			) as $dir => $path)
		{
			if(file_exists("$dir/$image.png"))
			{
				$image = "$path/$image.png";
				break;
			}

			if(file_exists("$dir/$image.gif"))
			{
				$image = "$path/$image.gif";
				break;
			}
		}

		$html = "<img src=\"$image\" width=\"16\" height=\"16\" title=\"$title\" alt=\"$action\" style=\"vertical-align: middle\"$class />";
	}
	else
	{
		//TODO: вынести хардкод
		$html = "<img src=\"http://s.wrk.ru/_bors/i16/$image\" width=\"16\" height=\"16\" title=\"$title\" alt=\"$alt\" class=\"flag\" />";
	}

	if($url = defval($params, 'url'))
		$html = "<a href=\"$url\">{$html}</a>";

	return $html;
}
