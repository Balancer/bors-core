<?php

class base_empty extends base_null
{
	private $_id;

	var $attr = array();

	function id() { return $this->_id; }
	function set_id($id) { return $this->_id = $id; }

	function __construct($id)
	{
		$this->set_id($this->initial_id = $id);
	}

	function get($name, $default = NULL)
	{
		if(method_exists($this, $name))
			return $this->$name();

		if(array_key_exists($name, $this->data))
			return $this->data[$name];

		if(array_key_exists($name, $this->attr))
			return $this->attr[$name];

		return $default;
	}

	function attr_preset()
	{
		return array_merge(parent::attr_preset(), array(
			'body_engine'	=> '',
		));
	}

	function storage_engine() { return ''; }
	function loaded() { return true; }
	function internal_uri() { return get_class($this).'://'.$this->id(); }
	function cache_clean() { }

	function auto_search_index() { return false; }
	function __toString() { return $this->class_name().'://'.$this->id(); }

	function attr($attr, $def = NULL) { return array_key_exists($attr, $this->attr) ? $this->attr[$attr] : $def; }
	function set_attr($attr, $value) { return $this->attr[$attr] = $value; }
	function load_attr($attr, $init)
	{
		if(array_key_exists($attr, $this->attr))
			return $this->attr[$attr];

		return $this->attr[$attr] = $init;
	}

}
