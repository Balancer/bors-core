<?php

class BorsXml
{
	private $parser;
	private $pointer;
	var $dom = array();

	private $in_tag = false;

	function __construct()
	{
		$this->pointer = &$this->dom;
		$this->parser = xml_parser_create();
		xml_set_object($this->parser, $this);
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->parser, "tag_open", "tag_close");
		xml_set_character_data_handler($this->parser, "cdata");
	}

	function parse($data)
	{
		xml_parse($this->parser, $data);
//		echo '==================================<br/>';
	}

	function tag_open($parser, $tag, $attributes)
	{
		unset($this->pointer['cdata']);
//		echo "Open tag $tag, attr=".print_r($attributes, true)."<br />\n";
		$attributes['_tag'] = $tag;
		$attributes['_parent'] = &$this->pointer;

		$this->pointer[$tag][] = &$attributes;

		$this->pointer = &$attributes;
		$this->in_tag = true;
	}

	function cdata($parser, $cdata)
	{
		if(!$this->in_tag)
			return;

		if(empty($this->pointer['cdata']))
			$this->pointer['cdata'] = dc(html_entity_decode($cdata, ENT_QUOTES, 'UTF-8'));
		else
			$this->pointer['cdata'] .= dc(html_entity_decode($cdata, ENT_QUOTES, 'UTF-8'));
	}

	function tag_close($parser, $tag)
	{
		$this->in_tag = false;
//		echo "Close tag $tag\n";
		$parent = &$this->pointer['_parent'];
		unset($this->pointer['_parent']);
		unset($this->pointer['_tag']);

		$this->pointer = &$parent;
	}
}
