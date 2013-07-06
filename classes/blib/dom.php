<?php

class blib_dom
{
	var $dom = NULL;

	static function from_url($url)
	{
		$html = blib_http::get($url);
		return self::from_html($html);
	}

	static function from_html($html)
	{
		$html = preg_replace('!<meta [^>]+?>!is', '', $html);
		$html = str_replace("\r", "", $html);

		$self = new blib_dom;

		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->encoding = 'UTF-8';
		$dom->loadHTML('<?xml encoding="UTF-8">' . $html);

		$self->dom = $dom;
		return $self;
	}

	function query_all($query)
	{
		$xpath = new DOMXPath($this->dom);
		$els = $xpath->query($query);
		return $els;
	}

	function query($query)
	{
		$els = $this->query_all($query);
		if(!$els)
			return blib_dom_element::null();

		return blib_dom_element::from_element($els->item(0));
	}

	function all_by_tag($tag_name)
	{
		return $this->dom->getElementsByTagName($tag_name);
	}

	function by_tag($tag_name)
	{
		$tags = $this->all_by_tag($tag_name);
//		var_dump($tags->item(0)->nodeValue);
		if(!$tags)
			return blib_dom_element::null();

		return blib_dom_element::from_element($tags->item(0));
	}
}
