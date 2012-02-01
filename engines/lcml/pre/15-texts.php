<?php

function lcml_texts($text)
{
	if(config('lcml_markdown'))
	{
		// markdown-like заголовки
		$text = preg_replace("!(\n|^)([^\n]+)\n={5,}\n!s", "$1[h1]$2[/h1]\n", $text);
		$text = preg_replace("!(\n|^)([^\n]+)\n-{5,}\n!s", "$1[h2]$2[/h2]\n", $text);
	}

	// Горизонтальный отчерк минусами, hr.
	$text = preg_replace('!^\-{3,}!m', '[hr]', $text);

	$text = preg_replace("/=== cut ===/","<table class=\"null w100p\"><tr><td width=\"20\"><hr/></td><td width=\"10\">✂</td><td><hr/></td></tr></table>", $text);

	for($i=5; $i>=1; $i--)
	{
		$pad = str_repeat('=', $i);
		$ih = $i + 1;
		eval("\$text = preg_replace(\"/^ *{$pad} (.+) {$pad} *\$/me\", \"'[h{$ih}]'.lcml(stripq('\$1')).'[/h{$ih}]'\", \$text);");
	}

	// Сноски
	$text = preg_replace("!^//\s+(.+?)$!me", "lcml('[reference]$1[/reference]');", $text);

	return $text;
}
