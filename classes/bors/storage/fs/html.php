<?php

class bors_storage_fs_html extends base_null
{
	private $html;
	private $obj;
	private $xml = NULL;
	function ext($key, $new_name = NULL)
	{
    	if(!$new_name)
        	$new_name = array_pop(explode('/', $key));

//		if(!$this->xml)
//			$this->xml = new SimpleXMLElement($this->html);

		$result = $this->xml->xpath($key);

		$value = array_shift($result);
		if($value)
		{
			$sub = array_pop(explode('/', $key));
			$value = preg_replace("!^<$sub>(.*?)</$sub>$!s", '$1', $value->asXML());
		}

		if($new_name == '-')
			return $value;

		$this->obj->set($new_name, $value, false);
	}

	private function __find($object)
	{
		$dir = $object->dir();
		$root = $object->document_root();
		$rel = secure_path(str_replace($root, '/', $dir));

		if(file_exists($file = "{$dir}/index.html"))
			return $file;

		if(file_exists($file = "{$dir}.html"))
			return $file;

		if($object->host() == bors()->server()->host())
		{
			foreach(bors_dirs() as $d)
			{
				if(file_exists($file = secure_path("{$d}/data/fs/{$rel}.html")))
					return $file;

				if(file_exists($file = secure_path("{$d}/data/fs/{$rel}/index.html")))
					return $file;
			}
		}
		else
		{
			$data = bors_vhost_data($object->host());
			if(file_exists($file = "{$data['bors_site']}/data/fs/{$rel}index.html"))
				return $file;

			if(file_exists($file = "{$data['bors_site']}/data/fs{$rel}.html"))
				return $file;
		}

		return false;
	}

	function load($object)
	{
		$file = $this->__find($object);
		if(!$file)
			return $object->set_loaded(false);

		if(!($html = @file_get_contents($file)))
			return $object->set_loaded(false);

		if($object->internal_charset() != 'utf-8')
			$html = ec($html);

		$html = str_replace("\r", "", $html);

		$this->obj = &$object;

		libxml_use_internal_errors(true);
		$sxe = simplexml_load_string($html);
		if(!$sxe)
		{
			echo "Failed loading XML<br/>\n";
			foreach(libxml_get_errors() as $error)
               	echo "\t", $error->message."<br/>\n";
		}

//		$this->html = $html;
		$this->xml = $sxe;

		$this->ext('head/title');
		$this->ext('head/page_title');

		if($style = $this->ext('head/style', '-'))
			$this->obj->merge_template_data_array('style', array($style));

//		$parents = explode(' ', $this->ext('head/parents', '-'));
//		if(empty($parents[0]))
		{
			$data = url_parse($object->url());
			if(($pd = dirname($data['path'])) != '/')
				$parents = array($pd.'/');
			else
				$parents = array('/');
		}

		$object->set_parents($parents, false);

//		$this->ext('nav_name');
//		$this->ext('description');

//    	$this->ext('copyr','copyright');
//    	$this->ext('author');
//    	$this->ext('author','copyright');
//  	$this->ext('type');
//    	$this->ext('create_time');
//  	$this->ext('style');
//    	$this->ext('template');
//    	$this->ext('color');
//    	$this->ext('logdir');
//    	$this->ext('cr_type');
//    	$this->ext('split_type');

//    	$this->ext('flags');

//    	$this->ext('long');
//    	$this->ext('short');
//    	$this->ext('start');
//    	$this->ext('file');
//    	$this->ext('forum_id');

		$object->set_source($this->ext('body', '-'), false);
		debug_log_var('data_file', $file);

		return $object->set_loaded(true);
	}

	function save($object)
	{
		bors_throw("index.html saving not implemented yet");
	}
}
