<?php

// Для тестов.
// Пример .hts — http://www.aviaport.ru/pages/2012/save-il-14/

bors_function_include('debug/log_var');

class bors_storage_htsu extends bors_storage
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

		return $this->obj->set($new_name, $m[1], false);
	}

	private function __find($object)
	{
		$ext = $object->get('hts_extension', 'htsu');

		if(preg_match("/\.$ext?$/", $object->id()) && file_exists($object->id()))
			return $object->id();

		$dir = $object->dir();
		$root = $object->document_root();
		$base = $object->_basename();
		$rel = secure_path(str_replace($root, '/', $dir));

//		echo "Find htsu for dir=$dir, root=$root, base=$base, rel=$rel<br/>\n";

		if(($ut = config('url_truncate')))
		{
			if(!preg_match("!/$ut(/|$)!", $rel))
				return false;

			$rel = preg_replace("!/$ut(/|$)!", '', $rel);
		}

		if($base && file_exists($file = "{$dir}/{$base}.{$ext}"))
			return $file;

		if($base && file_exists($file = "{$dir}/{$base}/main.{$ext}"))
			return $file;

		if($base && file_exists($file = "{$dir}/{$base}/index.{$ext}"))
			return $file;

//		if($base && file_exists($file = "{$dir}/{$base}"))
//			return $file;

		if(!$base && file_exists($file = "{$dir}/index.{$ext}"))
			return $file;

		if(!$base && file_exists($file = "{$dir}.{$ext}"))
			return $file;

		if($object->host() == bors()->server()->host())
		{
			foreach(bors_dirs() as $d)
			{
				if($base && file_exists($file = secure_path("{$d}/webroot/{$rel}/{$base}.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/webroot/{$rel}.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/webroot/{$rel}/index.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/webroot/{$rel}/main.{$ext}")))
					return $file;

				if($base && file_exists($file = secure_path("{$d}/data/webroot/{$rel}/{$base}.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/data/webroot/{$rel}.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/data/webroot/{$rel}/index.{$ext}")))
					return $file;

				if($base && file_exists($file = secure_path("{$d}/data/fs/{$rel}/{$base}.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/data/fs/{$rel}.{$ext}")))
					return $file;

				if(!$base  && file_exists($file = secure_path("{$d}/data/fs/{$rel}/main.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/data/fs/{$rel}/index.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/data/fs-hts/{$rel}.{$ext}")))
					return $file;

				if(!$base && file_exists($file = secure_path("{$d}/data/fs-hts/{$rel}/index.{$ext}")))
					return $file;
			}
		}
		else
		{
			$data = bors_vhost_data($object->host());
			if(file_exists($file = "{$data['bors_site']}/data/fs/{$rel}main.{$ext}"))
				return $file;

			if(file_exists($file = "{$data['bors_site']}/data/fs/{$rel}index.{$ext}"))
				return $file;

			if(file_exists($file = "{$data['bors_site']}/data/fs{$rel}.{$ext}"))
				return $file;
		}

		return false;
	}

	function load($object)
	{
		if(!($file = $object->get('htsu_file')))
			$file = $this->__find($object);

//		echo "Found hts at $file<br/>\n";

		if(!$file)
			return $object->set_is_loaded(false);

		// По дефолту в index.hts разрешёны HTML и все BB-теги.
		$object->set_html_disable(false, false);
		$object->set_lcml_tags_enabled(NULL, false);

		if(!($hts = @file_get_contents($file)))
			return $object->set_is_loaded(false);

		if($object->internal_charset() != 'utf-8')
			$hts = ec($hts);

		return $this->parse($hts, $object, $file);
	}

	function parse($hts, $object, $file = NULL)
	{
		$hts = str_replace("\r", "", $hts);

		$this->obj = &$object;

		$old = false;
		$this->hts = $hts;

		$this->ext('title');
		$this->ext('page_title');

		$parents = explode(' ', $this->ext('parents', '-'));
		if(empty($parents[0]) && $object->get('url_engine'))
		{
			$data = url_parse($object->url());
			if(($pd = dirname($data['path'])) != '/')
				$parents = array($pd.'/');
			else
				$parents = array('/');
		}

		$object->set_parents($parents, false);

		$this->ext('nav_name');
		$this->ext('description');

//    	$this->ext('copyr','copyright');
//    	$this->ext('author');
    	$this->ext('author','copyright');
    	$this->ext('type');
    	$this->ext('create_time', NULL, false);
    	$this->ext('modify_time', NULL, false);
    	$this->ext('style');
    	$this->ext('template');
		// Атрибут редиректа по ссылке при попытке показа данной страницы. Полезно для страниц-заглушек у которых должно быть своё имя в навигации, но показ которых не нужен.
    	$this->ext('show_redirect');
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

		$this->ext('tags', 'keywords_string');
		$this->ext('desc', 'description');
		$this->ext('nav', 'nav_name');

		if(!$object->create_time(true))
			$this->ext('created', 'create_time');

		if(!$object->create_time(true) && $file)
			// Внимание! Это не настоящий create time!
			$object->set('create_time', filectime($file), false);

		if(!$object->modify_time(true) && $file)
			$object->set('modify_time', filemtime($file), false);

		if(!$object->title_true())
			if(preg_match("/(^|\n)([^\n]+)\n(==+)\n/s", $this->hts, $m))
			{
//				var_dump($m);
				$this->hts = preg_replace("/(^|\n)([^\n]+)\n(==+)\n/s", "$1\n", $this->hts);
				$object->set_title($m[2], false);
			}

		if(!$object->title_true() && $file)
		{
			$ext = $object->get('hts_extension', 'htsu');
			$object->set('title', preg_replace("/\.$ext$/i", '', basename($file)).'.', false);
		}

		if($config_class = $this->ext('config', '-'))
		{
			$object->set_attr('config_class', $config_class);
			$object->_configure();
		}

//		$this->hts = preg_replace_callback('/^#(template_data)_(\w+)\s+(.+)$/m', array(&$this, '_set_callback'), $this->hts);
		$this->hts = preg_replace_callback('/^#call\s+(\w+)\s+(.+?)$/m', array(&$this, '_call_callback'), $this->hts);
		$this->hts = preg_replace_callback('/^#set\s+(\w+)\s+(.+?)$/m', array(&$this, '_set_callback'), $this->hts);
		$this->hts = preg_replace_callback('/^#set\s+(\w+)\[\]\s+(.+?)$/m', array(&$this, '_set_array_callback'), $this->hts);

	    $this->hts = preg_replace("!^\n+!",'',$this->hts);

		if(config('storage.htsu.do_php'))
		{
			require_once('inc/php.php');
			$this->hts = preg_replace("!\[php\](.+?)\[/php\]!es", "bors_php_fetch(stripq('$1'))", $this->hts);
		}

    	$object->set_source(preg_replace("!\n+$!","",$this->hts), false);

		debug_log_var('data_file', $file);

		return $object->set_is_loaded(true);
	}

	function _call_callback($matches)
	{
		$method = $matches[1];
		$args   = trim($matches[2]);

		if(preg_match('/^(\w+)\s+(.+)$/', $args, $m))
		{
			$x1 = $m[1];
			$x2 = $m[2];
		}
		else
		{
			$x1 = $args;
			$x2 = NULL;
		}

		call_user_func_array(array($this->obj, $method), array($x1, $x2));
		return '';
	}

	function _set_callback($matches)
	{
		$property = $matches[1];
		$value    = trim($matches[2]);
		call_user_func_array(array($this->obj, 'set'), array($property, $value, false));
		return '';
	}

	function _set_array_callback($matches)
	{
		$property = $matches[1];
		$value    = trim($matches[2]);
		$values = call_user_func_array(array($this->obj, 'get'), array($property, array()));

		$values[] = $value;

		call_user_func_array(array($this->obj, 'set'), array($property, $values, false));
		return '';
	}

	function save($object)
	{
		bors_throw("Try to save index.hts:<br/>\n".print_dd($object->data, true));
	}

	static function each($class_name, $where)
	{
		$iterator = new bors_storage_htsuIterator;
		$iterator->object = new $class_name(NULL);
		$iterator->root = $where['root'];
		$iterator->__class_name = $class_name;
		return $iterator;
	}
}
