<?php

// Просто жирный текст.

class bors_lcml_tag_pair_b extends bors_lcml_tag_pair
{
	function html($text)
	{
		return "<strong>".$this->lcml($text)."</strong>";
	}

	function text($text)
	{
		return "*".$this->lcml($text)."*";
	}
}
