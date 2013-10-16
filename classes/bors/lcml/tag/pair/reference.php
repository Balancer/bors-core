<?php

class bors_lcml_tag_pair_reference extends bors_lcml_tag_pair
{
	function html($text, &$params)
	{
		return "<small>//&nbsp;".substr(lcml('. '.$text), 2)."</small>";
	}
}
