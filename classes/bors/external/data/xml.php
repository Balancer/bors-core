<?php

class bors_external_data_xml extends bors_object_data
{
	function storage() { return $this; }

	function load($object)
	{
//		echo $this->xml_url()."\n";
		$xml = bors_lib_http::get_cached($this->xml_url(), $object->xml_cache_ttl());

		// Качалка перекодирует данные, но нам нужно поменять информацию об этом в заголовке
		$xml = preg_replace('!(<\?xml version=\S+ encoding=)"[^"]+"( \?>)!', '$1"utf-8"$2', $xml);
		$data = bors_lib_xml::xml2array($xml);

		$data = self::_array_path($data, $this->xml_root());
		if(!$data)
			return $object->set_is_loaded(false);

//		var_dump($data);

		foreach($object->xml_map() as $property => $idx)
		{
			if(is_numeric($property))
				$property = $idx;

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
		$object->set_is_loaded(true);
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

	function load_array($object, $where)
	{
		$objects = array();
		$class_name = $object->class_name();

		$xml = bors_lib_http::get_cached($this->xml_url(), $object->xml_cache_ttl());
		$data = bors_lib_xml::xml2array($xml);

		$data = self::_array_path($data, $this->xml_root());
		if(!$data)
			return $object->set_is_loaded(false);
//		print_dd($data);

		foreach($data as $item)
		{
			foreach($object->xml_map() as $property => $idx)
			{
				if(is_numeric($property))
					$property = $idx;

				if(preg_match('/^(.+)\|(.+)$/', $idx, $m))
				{
					$idx = $m[1];
					$func = $m[2];
					if(preg_match('/^(.+)\((.*)\)$/', $func, $mm))
					{
						$func = NULL;
						$eval = "\$val={$mm[1]}(\$val,{$mm[2]});";
//						echo "eval $eval\n";
					}
				}
				else
					$func = NULL;

				$val = self::_array_path($item, $idx.'/0/cdata');
				if($func)
					$val = $func($val);

				if(!empty($eval))
					eval($eval);

				$object->set($property, $val, false);
			}

			$object->set_is_loaded(true);
			$objects[] = $object;
			$object = new $class_name(NULL);
		}

		return $objects;
	}
}
