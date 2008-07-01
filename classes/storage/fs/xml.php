<?php

require_once('classes/inc/BorsXml.php'); 

class storage_fs_xml extends base_null
{
	function file($object)
	{
		$url = $object->url();
//		echo "url='$url'<br />\n";
		if(!$url)
			$url = $object->id();

		if(preg_match('!.*/$!', $url))
			$file = $url . 'index.xml';
		elseif(preg_match('!^(.*/[^/]+\.)\w{1,4}$!', $url, $m))
			$file = $m[1] . 'xml';
		else
			return $object->set_loaded(false);

		return config('storage_xml_base_dir', secure_path($_SERVER['DOCUMENT_ROOT'].'/../.data')).preg_replace('!^http://[^/]+!', '', $file);
	}

	function load($object)
	{
		$file = storage_fs_xml::file($object);
//		echo "Load {$object->url()} [{$object->id()}] as $file<br/>\n";

		if(!file_exists($file))
			return $object->set_loaded(false);

		$content = file_get_contents($file);
		$xml = &new BorsXml;
		$xml->parse($content);                                                                     

		$loaded = false;
//		print_d($xml->dom['bors'][0]);
		foreach($xml->dom['bors'][0] as $key => $data)
		{
			if($key == 'cdata')
				continue;
			
//			echo "$key -> ".count($data[0])."<br />";
			
			$loaded = true;

			if(empty($data[0]['i']))
				$value = $data[0]['cdata'];
			else
			{
				$value = array();
				$ordered = false;
				foreach($data[0]['i'] as $x)
				{
					if(!empty($x['cdata']))
					{
						if(empty($x['idx']))
							$value[] = $x['cdata'];
						else
						{
							$value[$x['idx']] = $x['cdata'];
							$ordered = true;
						}
					}
				}
				
				if($ordered)
					ksort($value);
				
//				print_d($value);
			}

			if(!is_array($value) && preg_match('!time!', $key) && !is_numeric($value))
				$value = strtotime($value);

			$object->{"set_$key"}($value, false);
		}
		
		return $object->set_loaded($loaded);
	}

	function save($object)
	{
		$file = storage_fs_xml::file($object);

		mkpath(dirname($file));

		$result = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<bors>\n";
		$fields = get_object_vars($object);
		$fields['stb_children'] = $object->children();
//		print_d($fields);
//		exit();
		foreach($fields as $field => $value)
		{
			if(!$value || !preg_match('!^stb_(\w+)$!', $field, $m))
				continue;
				
			$field = $m[1];
//			echo "Set $field to $value<br/>\n";
				
			$result .= "<$field>";
			if(is_array($value))
			{
				if($value)
				{
					$result .= "\n";
					foreach($value as $k => $v)
						$result .= "<i idx=\"$k\">".htmlspecialchars(str_replace("\r", '', $v))."</i>\n";
				}
			}
			else
			{
				if(preg_match('!time!', $field) && is_numeric($value))
					$value = gmdate('D, d M Y H:i:s', $value).' GMT';;

				$result .= htmlspecialchars(str_replace("\r", '', $value));
			}
			
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
	
	function delete($object)
	{
		$file = storage_fs_xml::file($object);
		if(!file_exists($file))
			return false;
			
		@unlink($file);
		@rmdir(dirname($file));
		return true;
	}
}
