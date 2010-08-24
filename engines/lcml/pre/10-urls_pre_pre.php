<?php

function lcml_urls_pre_pre($txt)
{
	$txt = preg_replace("!\[url\](.+?)\[/url\]!is", "[url=$1]$1[/url]", $txt);
//        $txt = preg_replace("!\[(http://\S+)\s*\"([^\"]+)\"\s*\]!is", "[url=$1]$2[/url]", $txt);
//        $txt = preg_replace("!\[(http://\S+)\s*(.*?)\s*\]!is", "[url=$1]$2[/url]", $txt);

	// Ссылки вида [jabber/ Jabber-клиенты]
	$txt = preg_replace("!\[([^\s\|\=]]+/)\s+([^\|\]]+?)\]!", "[url=$1]$2[/url]", $txt);

	// Ссылки вида [sparkweb/SparkWeb.html SparkWeb]
	$txt = preg_replace("!\[([^\s\|\]=]+/[^\s\|\]=]+)\s+([^\|\]]+?)\]!", "[url=$1]$2[/url]", $txt);

	return $txt;
}
