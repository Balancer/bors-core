<?php

require_once BORS_CORE.'/inc/bors/lists.php';

// Класс-заглушка (временная), так как пока модули по сути - обычные страницы.
class bors_module extends bors_page
{
	function body_engine()	{ return 'bors_bodies_page'; }

	function _is_static_def() { return false; }

	function html()
	{
		if($ttl = $this->get('body_cache_ttl') && class_exists('bors_cache'))
		{
			$ch = new bors_cache();
			$timestamp = max(filemtime($this->class_file()), filemtime($this->body_template_file()));
			if($ch->get('bors_module_cache', $this->internal_uri_ascii().'/'.$timestamp.'/'.serialize($this->args())))
				return $ch->last();
		}
		else
			$ch = NULL;

		$html = $this->html_code();
		if($ttl && $ch)
			$ch->set($html, $ttl);

		return $html;
	}

	function html_code()
	{
		try
		{
			$this->pre_show();
			$content = $this->body();
		}
		catch(Exception $e)
		{
			$content = bors_lib_exception::catch_html_code($e, ec("<div class=\"red_box\">Ошибка модуля ").$this->class_name()."</div>");
		}

		return $content;
	}

	static function show_mod($class_name, $args = NULL)
	{
		echo self::mod_html($class_name, $args);
	}

	static function show($args = array())
	{
		echo self::mod_html(get_called_class(), $args);
	}

	static function mod_html($class_name, $args = NULL)
	{
		if(preg_match('/^([\\\\\w]+)::(\w+)$/', $class_name, $m))
		{
			$class_name = $m[1];
			$func = $m[2];
		}
		else
			$func = 'html_code';

		if(preg_match('/^([a-z0-9]+)$/', $class_name))
			$class_name = 'bors_module_'.$class_name;

		if(preg_match('/^([a-z0-9]+)$/', $func))
			$func = $func.'_html';

		if(!is_array($args))
			$args = array('target' => $args);

		if(empty($args['view']) && !empty($args['object']))
			$args['view'] = $args['object'];

		$mod = bors_load_ex($class_name, NULL, $args);
		if(!$mod)
			return "Can't load module '$class_name'";

		return $mod->$func();
	}

	function body_data()
	{
		return array_merge(parent::body_data(), $this->args());
	}

	function __toString()
	{
		return $this->html();
	}
}
