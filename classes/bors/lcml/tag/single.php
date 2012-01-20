<?php

class bors_lcml_tag_single extends bors_lcml_tag
{
	function parse(&$params = array())
	{
		switch($this->lcml->output_type())
		{
			case 'text':
				return $this->text($params);
			case 'html_compact':
				return $this->html_compact($params);
			case 'html_simple':
				return $this->html_simple($params);
			case 'html':
			default:
				return $this->html($params);
		}
	}

	function html($params) { return ''; }
	function html_compact($params) { return $this->html(); }
	function html_simple($params) { return $this->html(); }
	function text($params) { return strip_tags($this->html()); }
}
