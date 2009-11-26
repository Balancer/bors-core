<?php

class bors_markup_lcml
{
	function parse($text)
	{
		require_once('engines/lcml/main.php');
		return lcml($text);
	}
}
