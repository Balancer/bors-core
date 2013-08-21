<?php

class b2_url extends blib_string
{
	var $engine_class_name = 'url_calling';
	var $page = NULL;

	function set_page($page)
	{
		$this->page = $page;
		return $this;
	}

	function set_engine($engine_class_name)
	{
		$this->engine_class_name = $engine_class_name;
		return $this;
	}

	function engine() { return bors_load($this->engine_class_name, NULL); }

	function __toString()
	{
		return $this->_value; // (string) $this->engine()->url($this->page);
	}

	function set_param($name, $value)
	{
//		var_dump($this->_value);
		$this->_value = blib_urls::replace_query($this->_value, $name, $value);
		return $this;
	}
}

function b2_url($url)
{
	return b2_url::factory($url);
}
