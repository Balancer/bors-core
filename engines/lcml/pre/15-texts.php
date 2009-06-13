<?php

function lcml_texts($text)
{
	$text = preg_replace('!\-{3,}!', '[hr]', $text);
	return $text;
}
