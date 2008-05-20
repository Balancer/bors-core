<?php

require_once('classes/inc/BorsXml.php'); 

class storage_fs_xml extends base_null
{
	function load($object)
	{
		$url = $object->url();
		if(preg_match('!.+/$!', $url))
			$file = $url . 'index.xml';
		elseif(preg_match('!^(.+/[^/]+\.)\w{1,3}$!', $url, $m))
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

			$object->{"set_$key"}($value, false);
		}
		
		return $object->set_loaded($loaded);
	}
}
