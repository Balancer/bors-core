<?php

class bors_lcml_tag_single extends bors_lcml_tag
{
	function parse(&$params = array())
	{
		$tag_value = @$params[$params['tag_name']];
		switch($this->lcml->output_type())
		{
			case 'text':
				return $this->text($tag_value, $params);
			case 'html_compact':
				return $this->html_compact($tag_value, $params);
			case 'html_simple':
				return $this->html_simple($tag_value, $params);
			case 'html':
			default:
				return $this->html($tag_value, $params);
		}
	}

	function html($text, &$params) { return ''; }
	function html_compact($text, &$params) { return $this->html($text, $params); }
	function html_simple($text, &$params) { return $this->html($text, $params); }
	function text($text, &$params) { return strip_tags($this->html($text, $params)); }
}
