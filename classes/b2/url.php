<?php

class b2_url extends b2_null
{
	function set_page($page)
	{
		$this->set_attr('page', $page);
		return $this;
	}

	function set_engine($engine_class_name)
	{
		$this->set_attr('engine_class_name', $engine_class_name);
		return $this;
	}

	function engine() { return bors_load($this->attr['engine_class_name'], NULL); }

	function __toString()
	{
		return $this->engine()->url($page);
	}
}

function b2_url($url)
{
	return b2_url::factory($url);
}
