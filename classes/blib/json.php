<?php

class blib_json
{
	// via http://php.net/manual/en/function.json-encode.php#105749
	static function encode_jsfunc($input=array(), $funcs=array(), $level=0)
	{
		foreach($input as $key=>$value)
		{
			if(is_array($value))
			{
				$ret = self::encode_jsfunc($value, $funcs, 1);
				$input[$key]=$ret[0];
				$funcs=$ret[1];
			}
			else
			{
				if(preg_match('/^\s*function\s*\(/', $value))
				{
					$func_key="#".uniqid()."#";
					$funcs[$func_key]=$value;
					$input[$key]=$func_key;
				}
			}
		}

		if($level==1)
			return array($input, $funcs);

		$input_json = str_replace('\/', '/', json_encode($input));
		foreach($funcs as $key => $value)
				$input_json = str_replace('"'.$key.'"', $value, $input_json);

		return $input_json;
	}

	// Генератор utf-8 json на php с поддержкой unicode 6
	// via http://habrahabr.ru/post/195806/
	static function encode53($data)
	{
		return preg_replace_callback('/\\\\ud([89ab][0-9a-f]{2})\\\\ud([c-f][0-9a-f]{2})|\\\\u([0-9a-f]{4})/i', function($val)
		{
			return html_entity_decode(
				empty($val[3]) ?
					sprintf('&#x%x;', ((hexdec($val[1])&0x3FF)<<10)+(hexdec($val[2])&0x3FF)+0x10000)
				:
					'&#x'.$val[3].';', ENT_NOQUOTES, 'utf-8'
			);
		}, json_encode($data));
	}

	static function file_update($file, $data, $mode = 0664)
	{
    	if(!$fh = fopen($file, 'a+'))
			throw new Exception("Can't open write {$file}");

	    if(!flock($fh, LOCK_EX))
			throw new Exception("Can't lock write {$file}");

		$fs = filesize($file);

		fseek($fh, 0);
		if($fs)
			$json = fread($fh, $fs);
		else
			$json = "";

//		echo "\n==============\nRead:\n$json\n==============";

		if($json && is_array($old_data = json_decode($json, true)))
			$data += $old_data;

//		echo "\n==============\nMerged:\n".print_r($data, true)."\n==============";

	    if(!ftruncate($fh, 0))
			throw new Exception("Can't truncate write {$file}");

	    fwrite($fh, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    	fclose($fh);

		clearstatcache(false, $file);

		if(file_exists($file))
			@chmod($file, $mode);

//		echo "\n==============\nWrote:\n".file_get_contents($file)."\n==============";

		return $data;
	}

	static function __dev()
	{
/*
		var_dump(self::file_update('test.json', ['a' => 1, 'b' => 2, 'c' => 4]));
		echo file_get_contents('test.json');
		var_dump(self::file_update('test.json', ['c' => 3, 'd' => 4]));
*/
		self::file_update('test2.json', [12345 => ['html' => 'text 12345_2']]);
		self::file_update('test2.json', [6789 => ['html' => 'text 6789']]);
	}

	static function __unit_test($suite)
	{
		$array = array(
			'name' => 'N51',
			'data' => array(1024,
				array(
					'y' => 2048,
					'events' => array(
						'mouseOver' => 'function() { $reporting.html(\'description of value\'); }'
					)
				),
				4096
			)
		);

		$suite->assertEquals('{"name":"N51","data":[1024,{"y":2048,"events":{"mouseOver":function() { $reporting.html(\'description of value\'); }}},4096]}', blib_json::encode_jsfunc($array));
	}
}
