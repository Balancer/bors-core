<?php

class blib_dom_element
{
	var $element = NULL;

	static function null()
	{
		return new blib_dom_element;
	}

	static function from_element($element)
	{
		$self = new blib_dom_element;
		$self->element = $element;
		return $self;
	}

	function html()
	{
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->encoding = 'UTF-8';
		$dom->appendChild($dom->importNode($this->element, true));
		return trim($dom->saveHTML());
	}

	function text()
	{
		return trim($this->element->nodeValue);
	}

	function attr($attr_name)
	{
		if($el = $this->element)
			return $el->getAttribute($attr_name);

		return NULL;
	}
}
