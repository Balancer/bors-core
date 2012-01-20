<?php

class bors_lcml_tag_pair extends bors_lcml_tag
{
	function parse($code, &$params = array())
	{
		switch($this->lcml->output_type())
		{
			case 'text':
				return $this->text($code, $params);
			case 'html_compact':
				return $this->html_compact($code, $params);
			case 'html_simple':
				return $this->html_simple($code, $params);
			case 'html':
			default:
				return $this->html($code, $params);
		}
	}
}
