<?php

class bors_lcml_tag_pair_reference extends bors_lcml_tag_pair
{
	function html($text, $params)
	{
		return "<small>// ".substr(lcml('. '.$text), 2)."</small>";
	}
}