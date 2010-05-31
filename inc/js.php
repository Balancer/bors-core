<?php

if(!function_exists('json_decode'))
{
	function json_decode($json)
	{
		// Author: walidator.info 2009
		// http://www.php.net/manual/en/function.json-decode.php
		$comment = false;
		$out = '$x=';

		for($i=0; $i<strlen($json); $i++)
		{
			if(!$comment)
			{
				if ($json[$i] == '{')		$out .= ' array(';
				else if ($json[$i] == '}')	$out .= ')';
				else if ($json[$i] == ':')	$out .= '=>';
				else						$out .= $json[$i];
			}
			else $out .= $json[$i];
			if ($json[$i] == '"') $comment = !$comment;
		}
		eval($out . ';');
		return $x;
	}
}

if(!function_exists('json_encode'))
{
	function json_encode($json)
	{
	}
}

function str2js($text)
{
	$out = "with(document){";

	$skip = false;

    foreach(split("\n", $text) as $s)
	{
		if($skip)
		{
			if(preg_match('!^(.*)</script>$!', $s, $m))
			{
				$out .= $m[1]."\n";
				$skip = false;
			}
			else
				$out .= $s."\n";
		}
		else
		{
			if(preg_match('!^<script>(.*)$!', $s, $m))
			{
				$out .= $m[1]."\n";
				$skip = true;
			}
			else
		        $out .= "write(\"".addslashes($s)."\");\n";
		}
	}

	$out = preg_replace('!<script>(.+?)</script>!', "\"+$1+\"", $out);

	return $out."}";
}

