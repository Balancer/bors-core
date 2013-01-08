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
				if(substr($value,0,10)=='function()')
				{
					$func_key="#".uniqid()."#";
					$funcs[$func_key]=$value;
					$input[$key]=$func_key;
				}
			}
		}

		if($level==1)
			return array($input, $funcs);

		$input_json = json_encode($input);
		foreach($funcs as $key=>$value)
				$input_json = str_replace('"'.$key.'"', $value, $input_json);

		return $input_json;
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
