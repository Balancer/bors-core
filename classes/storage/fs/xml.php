<?php

require_once('classes/inc/BorsXml.php'); 

class storage_fs_xml extends base_null
{
	function load($object)
	{
		$url = $object->url();
		if(!$url)
			$url = $object->id();
		if(preg_match('!.+/$!', $url))
			$file = $url . 'index.xml';
		elseif(preg_match('!^(.*/[^/]+\.)\w{1,4}$!', $url, $m))
			$file = $m[1] . 'xml';
		else
			return $object->set_loaded(false);

		$file = config('storage_xml_base_dir', secure_path($_SERVER['DOCUMENT_ROOT'].'/../.data')).preg_replace('!^http://[^/]+!', '', $file);

		if(!file_exists($file))
			return $object->set_loaded(false);

		$content = file_get_contents($file);
		$xml = &new BorsXml;
		$xml->parse($content);                                                                     

		$loaded = false;
		foreach($xml->dom['bors'][0] as $key => $data)
		{
			if($key == 'cdata')
				continue;
				
			$loaded = true;

			if(count($data[0]) == 1)
				$value = $data[0]['cdata'];
			else
			{
				$value = array();
				$ordered = false;
				foreach($data[0]['i'] as $x)
				{
					if(empty($x['idx']))
						$value[] = $x['cdata'];
					else
					{
						$value[$x['idx']] = $x['cdata'];
						$ordered = true;
					}
				}
				
				if($ordered)
					ksort($value);
			}

			if(!is_array($value) && preg_match('!time!', $key))
				$value = strtotime($value);

			$object->{"set_$key"}($value, false);
		}
		
		return $object->set_loaded($loaded);
	}

	function save($object)
	{
		$url = $object->url() ? $object->url() : $object->id();
		if(preg_match('!.+/$!', $url))
			$file = $url . 'index.xml';
		elseif(preg_match('!^(.*/[^/]+\.)\w{1,4}$!', $url, $m))
			$file = $m[1] . 'xml';
		else
			return false;

		$file = config('storage_xml_base_dir', secure_path($_SERVER['DOCUMENT_ROOT'].'/../.data')).preg_replace('!^http://[^/]+!', '', $file);

		if(!file_exists($file))
			return false;

		$result = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<bors>\n";
		foreach(get_object_vars($object) as $field => $value)
		{
			if(!$value || !preg_match('!^stb_(\w+)$!', $field, $m))
				continue;
				
			$field = $m[1];
				
			$result .= "<$field>";
			if(is_array($value))
			{
				foreach($value as $k => $v)
					$result .= "<i idx=\"$k\">".htmlspecialchars($v)."</i>";
			}
			else
				$result .= htmlspecialchars($value);
			
			$result .= "</$field>\n";
		}
		
		$result .= "</bors>\n";
		
//		exit($result);

		@mkdir(dirname($file), 0777, true);
		@chmod(dirname($file), 0777);
		file_put_contents($file, $result);
		@chmod($file, 0666);
		
		return true;
	}
}
