<?php

// Обновлённая версия старой LCML разметки.

require_once('engines/lcml/tags.php');
require_once('engines/lcml/funcs.php');

function lcml($text, $params = array())
{
	static $lc = false;
	if($lc === false)
		$lc = new bors_lcml($params);

	$lc->set_p('level', $lc->p('level')+1);
	$lc->set_p('prepare', popval($params, 'prepare'));
	$save_tags = $lc->p('only_tags');
	if(!empty($params['only_tags']))
		$lc->set_p('only_tags', $params['only_tags']);
	if($lc->p('level') == 1)
	{
		$lc->set_params($params);
	}
	$res = $lc->parse($text);
	$lc->set_p('only_tags', $save_tags);
	$lc->set_p('level', $lc->p('level')-1);

	return $res;
}

function lcml_h($string)
{
	$se = config('lcml_tags_enabled');
	$sd = config('lcml_tags_disabled');
	config_set('lcml_tags_enabled', NULL);
	config_set('lcml_tags_disabled', NULL);
	$result = lcml($string, array(
			'cr_type' => 'none',
			'sharp_not_comment' => true,
			'html_disable' => false,
			'nocache' => true,
	));
	config_set('lcml_tags_enabled', $se);
	config_set('lcml_tags_disabled', $sd);
	return $result;
}

// lcml с поддержкой html и пустая строка — параграф
function lcml_hp($string)
{
	$se = config('lcml_tags_enabled');
	$sd = config('lcml_tags_disabled');
	config_set('lcml_tags_enabled', NULL);
	config_set('lcml_tags_disabled', NULL);
	$result = lcml($string, array(
			'cr_type' => 'empty_as_para',
			'sharp_not_comment' => true,
			'html_disable' => false,
			'nocache' => true,
	));
	config_set('lcml_tags_enabled', $se);
	config_set('lcml_tags_disabled', $sd);
	return $result;
}

function lcmlbb($string) { return lcml_bb($string); } // Нужно для совместимости со старым кодом.
function lcml_bb($string)
{
	return lcml($string, array(
			'cr_type' => 'save_cr',
			'forum_type' => 'punbb',
			'sharp_not_comment' => true,
			'html_disable' => 'full',
			'nocache' => true,
	));
}

function lcml_bbh($string)
{
	$se = config('lcml_tags_enabled');
	$sd = config('lcml_tags_disabled');
	config_set('lcml_tags_enabled', NULL);
	config_set('lcml_tags_disabled', NULL);
	$result = lcml($string, array(
			'cr_type' => 'save_cr',
			'forum_type' => 'punbb',
			'sharp_not_comment' => true,
			'html_disable' => false,
			'nocache' => true,
	));
	config_set('lcml_tags_enabled', $se);
	config_set('lcml_tags_disabled', $sd);
	return $result;
}

function lcml_smart($string)
{
	$se = config('lcml_tags_enabled');
	$sd = config('lcml_tags_disabled');
	config_set('lcml_tags_enabled', NULL);
	config_set('lcml_tags_disabled', NULL);
	$result = lcml($string, array(
			'cr_type' => 'smart',
			'sharp_not_comment' => true,
			'html_disable' => false,
			'nocache' => true,
	));
	config_set('lcml_tags_enabled', $se);
	config_set('lcml_tags_disabled', $sd);
	return $result;
}

function lcml_tag_disabled($tag)
{
	if(@in_array('img', $enabled = config('lcml_tags_enabled')))
		return false;

	if(@in_array('img', config('lcml_tags_disabled')))
		return true;

	return !empty($enabled);
}

function html2bb($text, $args = array())
{
	$url		= defval($args, 'origin_url');	// Ссылка оригинального HTML. Нужна для вычисления относительных ссылок
	$strip_forms= defval($args, 'strip_forms');	// Выкинуть формы

	if($strip_forms)
		$text = preg_replace('!<form.+</form>!is', '', $text);

	$text = preg_replace("!<(\w+)><(\w+)>(.+?)</\\1></\\2>!is", "<$1><$2>$3</$2></$1>", $text); // бывает и такой изврат: <b><i>..</b></i>

	$text = preg_replace("!<font color=\"(blue)\">(.+?)</font>!is", "[$1]$2[/$1]", $text);
	$text = preg_replace("!<p>(.+?)</p>!is", "\n$1\n", $text);

	foreach(explode(' ', 'b em i li nobr ol strike u ul') as $tag)
	{
		$text = preg_replace("!<$tag>(.+?)</$tag>!is", "[$tag]$1[/$tag]", $text);
		$text = preg_replace("!<$tag [^>]+>(.+?)</$tag>!is", "[$tag]$1[/$tag]", $text);
	}

	foreach(array('strong' => 'b') as $h => $b)
	{
		$text = preg_replace("!<$h>(.+?)</$h>!is", "[$b]$1[/$b]", $text);
		$text = preg_replace("!<$h [^>]+>(.+?)</$h>!is", "[$b]$1[/$b]", $text);
	}

	$text = preg_replace("!<div [^>]*>\s*(.+?)\s*</div>!is", "\n$1\n", $text);
	$text = preg_replace("!<div>\s*(.*?)\s*</div>!is", "\n$1\n", $text);
	$text = preg_replace("!<p [^>]+>(.+?)</p>!is", "\n$1\n", $text);
	$text = preg_replace("!<p>!i", "\n\n", $text);
	$text = preg_replace("!<o:[^>]+>!i", "", $text);
	$text = preg_replace("!</o:[^>]+>!i", "", $text);
	$text = preg_replace("!<noindex>!i", "", $text);
	$text = preg_replace("!</noindex>!i", "", $text);
	$text = preg_replace("!<br\s*/?>!", "\n", $text);


	$text = preg_replace("!(<a [^>]*href=\")(/.+?)(\"[^>]*?>)!ie", '"$1" . url_relative_join("$url", "$2") . "$3";', $text);
	$text = preg_replace("!(<a [^>]*href=)([^\"']\S+)( [^>]+>)!ie", '"$1" . url_relative_join("$url", "$2") . "$3";', $text);

	$text = preg_replace('!<div style="text-align: center">(.+?)</div>!is', '[center]$1[/center]', $text);

	// blogger.com, обвязка сообщений
	$text = preg_replace('!<div class="separator" style="clear: both; text-align: center;">(.*?)</div>!s', '[div align="center"]$1[/div]', $text);
	$text = preg_replace('!<div class="blogger-post-footer">(.*?)</div>!s', '[div]$1[/div]', $text);

	// blogger.com, исправление картинок вида src="http://%D0%BD%D1%8E%D1%81%D0%B0%D0%B9%D1%82
	$text = preg_replace('!="(http://%D0[^"]+)"!e', "'=\"'.urldecode(\"$1\").'\"'", $text);

	$text = preg_replace("!<a [^>]*href=\"([^\"]+)\"[^>]*>(.*?)</a>!is", '[url=$1]$2[/url]', $text);
	$text = preg_replace('!(<img ([^>]+)>)!ise', 'lcmlbb_parse_img(stripslashes("$1"));', $text);

	$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

	$text = preg_replace('!<lj\-embed id="(\d+)" />!ise', "lcmlbb_lj_embed(\"$1\", '$url');", $text);
	$text = str_replace('embed', 'xx', $text);

	require_once('pre/03-external_code.php');
	$text = lcml_external_code($text);

	$text = preg_replace("/^\s+$/m", '', $text);

	$text = preg_replace("!\n(&nbsp;| )\n!", "\n\n", $text);
	$text = preg_replace("!\n{2,}!", "\n\n", $text);

//	if(config('is_debug')) echo "===$text===\n\n";

	// Нормализуем картинки, когда они же — ссылки на полноразмер.
	// [url=http://fap.to/images/full/43/495/495898172.jpg][img]http://fap.to/images/full/43/495/495898172.jpg[/img][/url]
	$text = preg_replace('!\[url=([^\]]+)\]\[img\]\1\[/img\]\[/url\]!is', "[img url=\"$1\" use_cache=\"1\" description='<a href=\"$1\">оригинал</a> | <a href=\"%IMAGE_PAGE_URL%\">кеш</a>' htmldecode=\"1\" ]", $text);

	// Ссылки на внешние страницы автоматизируем
	// [url=http://2.bp.blogspot.com/--0AOeJRq694/T8koZzdmfzI/AAAAAAAAGDQ/rzvNIj3EkPg/s1600/oMetArt_Preseting-Ashley_Ashley-Doll_by_Emslie_medium_0016.jpg][img]http://2.bp.blogspot.com/--0AOeJRq694/T8koZzdmfzI/AAAAAAAAGDQ/rzvNIj3EkPg/s640/oMetArt_Preseting-Ashley_Ashley-Doll_by_Emslie_medium_0016.jpg[/img][/url]
	$text = preg_replace('!\[url=([^\]]+\.jpe?g)\]\[img\]([^\[]+?)\[/img\]\[/url\]!is', "[img url=\"$1\" href=\"$1\" use_cache=\"1\" description='<a href=\"$1\">оригинал</a> | <a href=\"%IMAGE_PAGE_URL%\">кеш</a>' htmldecode=\"1\" ]", $text);

//	if(config('is_debug')) echo "===$text===\n\n";
	return trim($text);
}

function lcmlbb_lj_embed($id, $url)
{
	$html = bors_lib_http::get($url);
//<iframe src="http://lj-toys.com/?auth_token=sessionless:1279962000:embedcontent:13110916%2685%26:ada38ef9ec102f7b5cd98ad58fd9fb097c6c4aa0&amp;moduleid=85&amp;preview=&amp;journalid=13110916" width="640" height="385" frameborder="0" class="lj_embedcontent" name="embed_13110916_85"></iframe>
	if(!preg_match("!(<iframe src=\"[^\"]+?moduleid=$id.+?</iframe>)!is", $html, $m))
		return ec("\n[i][red]Видеоролик со страницы [url]{$url}[/url] не удалось импортировать[/red][/i]\n");

	return save_format($m[1]);
}

function lcmlbb_parse_img($tag)
{
	//alt="" width="158" height="240" border="0" src="http://pics.livejournal.com/idolomantis/pic/0003fg6a/s320x240"
//	echo "img attrs = $tag\n";
	if(class_exists('DOMDocument'))
	{
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML('<?xml encoding="UTF-8">' . $tag);
		$x = $dom->getElementsByTagName('img')->item(0);
		return "[img]{$x->getAttribute('src')}[/img]";
	}

	// Если нет DOMDocument, то ручной костыль.
	if(preg_match('!src=\"(.+?)\"!', $tag, $m))
		return "[img]{$m[1]}[/img]";

	return $tag;
}

function url_relative_join($url_main, $url_rel)
{
	if($url_rel[0] == '/')
		return preg_replace('!^(http://[^/]+).+?$!', '$1', $url_main).$url_rel;

	return $url_main . $url_rel;
}
