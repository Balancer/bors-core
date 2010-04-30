<?php

class bors_markup_lcmlbbh
{
	function parse($text)
	{
		require_once('engines/lcml/main.php');
		return lcml_bbh($text);
	}
}
