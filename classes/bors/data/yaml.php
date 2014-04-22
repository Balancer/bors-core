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
		{
			if($ignore_errors)
				$data = @yaml_parse($string);
			else
				$data = yaml_parse($string);
		}
		else
		{
//			require_once config('symphony.components_path', '/usr/share/php/SymfonyComponents').'/YAML/sfYamlParser.php';
//			Перенесено в Composer
			if(!class_exists('Symfony\Component\Yaml\Yaml'))
				bors_throw("Can't find yaml extension or Symfony\Component\Yaml. Go to composer directory at BORS_CORE level and execute composer require symfony/yaml=*");

			try {
				$data = Symfony\Component\Yaml\Yaml::parse($string);
			} catch(Exception $e)
			{
				$data = NULL;
				if(!$ignore_errors)
					bors_throw("Yaml parse error for string:<xmp>$string</xmp>;".blib_exception::factory($e)->message());
			}
		}

		return $data;
	}

	static function __unit_test($suite)
	{
		$suite->assertEquals(array('test' => 'data1', 'test2' => array('var1', 'var2')), self::parse("test: data1\ntest2:\n\t- var1\n\t- var2"));
	}
}
