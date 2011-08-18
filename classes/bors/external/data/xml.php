<?php

class bors_external_data_xml extends bors_object_data
{
	function storage() { return $this; }

	function load($object)
	{
		$xml = bors_lib_http::get_cached($this->xml_url(), $object->xml_cache_ttl());
		$data = bors_lib_xml::xml2array($xml);

		$data = self::_array_path($data, $this->xml_root());
		if(!$data)
			return $object->set_loaded(false);
//		print_dd($data);

		foreach($object->xml_map() as $property => $idx)
		{
			if(preg_match('/^(.+)\|(.+)$/', $idx, $m))
			{
				$idx = $m[1];
				$func = $m[2];
				if(preg_match('/^(.+)\((.*)\)$/', $func, $mm))
				{
					$func = NULL;
					$eval = "\$val={$mm[1]}(\$val,{$mm[2]});";
//					echo "eval $eval\n";
				}
			}
			else
				$func = NULL;

			$val = self::_array_path($data, $idx);
			if($func)
				$val = $func($val);
			if(!empty($eval))
				eval($eval);

			$object->set($property, $val, false);
		}

//		print_dd($object->data);
		$object->set_loaded(true);
		return true;
	}

	function xml_cache_ttl() { return 3600; }

	function _array_path($data, $path)
	{
		foreach(explode('/', $path) as $idx)
			$data = $data[$idx];

		return $data;
	}

	function array_key_extract($array, $key, $plain = false)
	{
		$result = array();
		foreach($array as $x)
		{
			$idx = $x[$key];
			if($plain)
			{
				foreach($x as $k => $v)
				{
					if(is_array($v))
						$v = array_pop($v);
					if(is_array($v) && array_key_exists('cdata', $v))
						$v = $v['cdata'];

					$x[$k] = $v;
				}
			}

			$result[$idx] = $x;
		}

		return $result;
	}
}
