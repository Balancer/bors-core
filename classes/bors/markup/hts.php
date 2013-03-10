<?php

class bors_markup_hts extends base_object
{
	var $object;

	static function factory($text = NULL, $args = array())
	{
		$object = new bors_markup_hts(NULL);
		$object->set_attrs($args);

		if($text)
			$object->parse_source($text, false);

		return $object;
	}

	function parse_source($source, $update)
	{
		parent::set_source($source, $update);

		$foo = new bors_storage_htsu(NULL);
		$foo->parse($source, $this);

		return $source;
	}

	function text()
	{
		return $this->source();
	}

	function html()
	{
		return lcml_bbh($this->source());
	}
}
