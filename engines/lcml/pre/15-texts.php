<?php

function lcml_texts($text)
{
	$text = preg_replace('!^\-{3,}!m', '[hr]', $text);

	$text = preg_replace("/=== cut ===/","<table class=\"null w100p\"><tr><td width=\"20\"><hr/></td><td width=\"10\">✂</td><td><hr/></td></tr></table>", $text);

	for($i=5; $i>=1; $i--)
	{
		$pad = str_repeat('=', $i);
		$ih = $i + 1;
		eval("\$text = preg_replace(\"/^ *{$pad} (.+) {$pad} *\$/me\", \"'[h{$ih}]'.lcml(stripq('\$1')).'[/h{$ih}]'\", \$text);");
	}

	return $text;
}
