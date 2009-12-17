<?php

class storage_fs_htsu extends base_null
{
	private $hts;
	private $obj;
	function ext($key, $new_name = NULL)
	{
    	if(!$new_name)
        	$new_name = $key;

		if(preg_match("!^#$key +(.*?)$!m", $this->hts, $m))
			$this->hts = preg_replace("!(^|\n)#$key +.*?(\n|$)!", '$1$2', $this->hts);
		elseif(preg_match("!^#$key()$!m", $this->hts, $m))
			$this->hts = preg_replace("!(^|\n)#$key(\n|$)!", '$1$2', $this->hts);
		else
			return;

//		echo "Extracted for ($key,$new_name) = '{$m[1]}'<br>";

		if($new_name == '-')
			return $m[1];
		else
			return $this->obj->set($new_name, $m[1], false);
	}

	private function __find($object)
	{
		$dir = $object->dir();
		$root = $object->document_root();
		$rel = secure_path(str_replace($root, '/', $dir));

		if(file_exists($file = "{$dir}/index.htsu"))
			return $file;

		if(file_exists($file = "{$dir}.htsu"))
			return $file;

		if($object->host() == $_SERVER['HTTP_HOST'])
		{
			foreach(bors_dirs() as $d)
			{
				if(file_exists($file = secure_path("{$d}/data/fs/{$rel}.htsu")))
					return $file;

				if(file_exists($file = secure_path("{$d}/data/fs/{$rel}/index.htsu")))
					return $file;

				if(file_exists($file = secure_path("{$d}/data/fs-hts/{$rel}.htsu")))
					return $file;

				if(file_exists($file = secure_path("{$d}/data/fs-hts/{$rel}/index.htsu")))
					return $file;
			}
		}
		else
		{
			$data = bors_vhost_data($object->host());
			if(file_exists($file = "{$data['bors_site']}/data/fs/{$rel}index.htsu"))
				return $file;

			if(file_exists($file = "{$data['bors_site']}/data/fs{$rel}.htsu"))
				return $file;
		}

		return false;
	}

	function load($object)
	{
		$file = $this->__find($object);
		if(!$file)
			return $object->set_loaded(false);

		// По дефолту в index.hts разрешёны HTML и все BB-тэги.
		$object->set_html_disable(false, false);
		$object->set_lcml_tags_enabled(NULL, false);

		if(!($hts = @file_get_contents($file)))
			return $object->set_loaded(false);

		if($object->internal_charset() != 'utf-8')
			$hts = dc($hts, 'utf-8', $object->internal_charset());

		$hts = str_replace("\r", "", $hts);

		$this->obj = &$object;

		$old = false;
		$this->hts = $hts;

		$this->ext('title');

		$parents = explode(' ', $this->ext('parents', '-'));
		if(empty($parents[0]))
		{
			$data = url_parse($object->url());
			$parents = array(dirname($data['path']).'/');
		}

		$object->set_parents($parents, false);

		$this->ext('nav_name');

//    	$this->ext('copyr','copyright');
//    	$this->ext('author');
    	$this->ext('author','copyright');
    	$this->ext('type');
    	$this->ext('create_time');
    	$this->ext('style');
    	$this->ext('template');
    	$this->ext('color');
    	$this->ext('logdir');
    	$this->ext('cr_type');
    	$this->ext('split_type');

    	$this->ext('flags');

    	$this->ext('long');
    	$this->ext('short');
    	$this->ext('start');
    	$this->ext('file');
    	$this->ext('forum_id');

	    $this->hts = preg_replace("!^\n+!",'',$this->hts);
    	$object->set_source(preg_replace("!\n+$!","",$this->hts), false);

		return $object->set_loaded(true);
	}

	function save($object)
	{
		debug_exit("Try to save index.hts");
	}
}
