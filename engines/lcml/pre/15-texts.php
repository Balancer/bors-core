<?php

function lcml_texts($text)
{
	$text = preg_replace('!^\-{3,}!m', '[hr]', $text);
	return $text;
}
