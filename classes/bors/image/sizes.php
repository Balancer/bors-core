<?php

class bors_image_sizes extends base_list
{
	function named_list()
	{
		return array(
			1 => '640x480',
			2 => '800x700',
			3 => '1024x768',
			4 => '1280x1024',
			5 => '1600x1200',
			'default' => 3,
		);
	}
}
