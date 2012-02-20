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

		$data = self::parse($content);

		return array(
			'data' => $data,
			'attrs' => array(
				'file' => $file,
				'filemtime' => $mtime,
			)
		);
	}

	static function parse($string, $ignore_errors = false)
	{
		$string = str_replace("\t", '    ', $string);

		if(function_exists('yaml_parse'))
			$data = yaml_parse($string);
		else
		{
			require_once '/usr/share/php/SymfonyComponents/YAML/sfYamlParser.php';
			$yaml = new sfYamlParser();
			try {
				$data = $yaml->parse($string);
			} catch(Exception $e)
			{
				$data = NULL;
				if(!$ignore_errors)
					bors_throw("Yaml parse error for string '$string'");
			}
		}

		return $data;
	}
}
