<?php

/**
	Пример использования:
	$data = bors_data_yaml::load('classes/aviaport/admin/events/fast.yaml');
	var_dump($data);
*/

class bors_data_yaml extends bors_data_meta
{
	static function load($file)
	{
		$data = self::read($file);
		if(is_null($data))
			return NULL;

		extract($data);

		// yaml_parse не понимает табы в начале строк. Меняем все табы на 4 пробела
		$content = str_replace("\t", '    ', $content);

		$data = yaml_parse($content);

		return array(
			'data' => $data,
			'attrs' => array(
				'file' => $file,
				'filemtime' => $mtime,
			)
		);
	}
}
