<?php

function bors_unplural($s)
{
	if(preg_match('/rss$/', $s, $m)) // xrss -> xrss. Исключения не трогаем
		return $s;
	if(preg_match('/^(.+)ies$/', $s, $m)) // companies -> company
		return $m[1].'y';
	if(preg_match('/^(.+s)es$/', $s, $m)) // newses -> news
		return $m[1];
	if(preg_match('/^(.+)s$/', $s, $m)) // planes -> plane
		return $m[1];
	return $s;
}
