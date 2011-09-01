<?php

function lcml_text($txt)
{ 
//	$txt = preg_replace("!(^|\s|\"|:|')\*(\S.*?\S)\*(?=(\s|\.|,|\"|'|:|;|$|\?))!","$1<b>$2</b>",$txt);
//	$txt = preg_replace("!(^|\s|\"|:|')_(\S.*?\S)_(?=(\s|\.\s|,\s|\"|:|;|'\s|$|\?))!","$1<i>$2</i>",$txt);

	$txt = preg_replace("!(^|[\s\":'])\*(\S[^*]*\S)\*([\s\.,\"':;\?]|$)!","$1<b>$2</b>$3",$txt);
	$txt = preg_replace("!(^|[\s\":'])_(\S[^_]*\S)_([\s\.,\"':;\?]|$)!","$1<i>$2</i>$3",$txt);

	//TODO: медленно на больших текстах.
//	$txt = preg_replace("!(\W)\*(\S[^*]*\S)\*(\W)!u", "$1<strong>$2</strong>$3",$txt);
//	$txt = preg_replace("!(\W)_(\S[^_]*\S)_(\W)!u"  , "$1<em>$2</em>$3",$txt);

	$txt = preg_replace("!^(//\s+.+)$!m","<small>$1 </small><br/>",$txt);

	$txt = preg_replace("!\^(\-?[\d\.]+)!","<sup>$1</sup>",$txt);
	$txt = str_ireplace("^o","°",$txt);
	$txt = str_ireplace("(C)","&copy;",$txt);
//	$txt = preg_replace("!_(\-?[\d\.]+)!","<sub>$1</sub>",$txt);

	$txt = preg_replace("!<<!", "&laquo;", $txt);
	$txt = preg_replace("!>>!", "&raquo;", $txt);
//	$txt = preg_replace("!&lt;&lt;!", "&laquo;", $txt);
//	$txt = preg_replace("!&gt;&gt;!", "&raquo;", $txt);

	$txt = str_replace(' -- ', ' &mdash; ', $txt);

	$txt = str_replace('[p]', '<p/>', $txt);

	return $txt;
}
