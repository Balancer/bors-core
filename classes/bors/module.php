<?php

require_once('inc/bors/lists.php');

// Класс-заглушка (временная), так как пока модули по сути - обычные страницы.
class bors_module extends bors_page
{
	function body_engine()	{ return 'bors_bodies_page'; }

	function html()
	{
		if($ttl = $this->get('body_cache_ttl'))
		{
			$ch = new bors_cache();
			if($ch->get('body_cache_ttl', $this->internal_uri_ascii().'/'.serialize($this->args())))
				return $ch->last();
		}

		$html = $this->html_code();
		if($ttl)
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

	static function mod_html($class_name, $args = NULL)
	{
		if(preg_match('/^(\w+)::(\w+)$/', $class_name, $m))
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

		$mod = bors_load_ex($class_name, NULL, $args);
		return $mod->$func();
	}

	function body_data()
	{
		return array_merge(parent::body_data(), $this->args());
	}
}
