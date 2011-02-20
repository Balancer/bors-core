<?php

function lcml_urls_pre_pre($txt)
{
	$txt = preg_replace("!\[url\](www\..+?)\[/url\]!is", "[url=http://$1]$1[/url]", $txt);
	$txt = preg_replace("!\[url\](.+?)\[/url\]!is", "[url=$1]$1[/url]", $txt);
//        $txt = preg_replace("!\[(http://\S+)\s*\"([^\"]+)\"\s*\]!is", "[url=$1]$2[/url]", $txt);
//        $txt = preg_replace("!\[(http://\S+)\s*(.*?)\s*\]!is", "[url=$1]$2[/url]", $txt);


	// Большой жирный BB-code костыль для
	// [url=/catalogue/category/24/ target=_blank]фаст-фуд[/url]
	// Иначе следующие ссылки такое поменяют
	$txt = preg_replace("!\[url=([^\s^\]]+) target=(\w+)\](.+?)\[/url\]!is", "<a href=\"$1\" target=\"$2\">$3</a>", $txt);

	// Ссылки вида [jabber/ Jabber-клиенты]
	$txt = preg_replace("!\[([^\s\|\]]+/)\s+([^\|\]]+?)\]!", "[url=$1]$2[/url]", $txt);

	// Ссылки вида [sparkweb/SparkWeb.html SparkWeb]
	$txt = preg_replace("!\[([^\s\|\]]+/[^\s\|\]]+)\s+([^\|\]]+?)\]!", "[url=$1]$2[/url]", $txt);

	return $txt;
}
