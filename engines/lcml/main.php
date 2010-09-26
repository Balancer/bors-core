<?php

// Обновлённая версия старой LCML разметки.

require_once('engines/lcml/tags.php');
require_once('engines/lcml/funcs.php');

class bors_lcml
{
	private $_params = array();
	private static $data;

	function __construct($params = array())
	{
		$this->_params = $params;
		bors_lcml::init();
	}

	function p($key, $def = NULL) { return empty($this->_params[$key]) ? $def : $this->_params[$key]; }
	function set_p($key, $value) { $this->_params[$key] = $value; return $this; }

	private static function memcache()
	{
		static $mch = NULL;
		if($mch)
			return $mch;

		return $mch = new BorsMemCache();
	}

	static function init()
	{
		if(!empty(bors_lcml::$data))
			return;

		bors_lcml::$data['pre_functions'] = array();
		bors_lcml::actions_load('pre', bors_lcml::$data['pre_functions']);

		bors_lcml::$data['post_functions'] = array();
		bors_lcml::actions_load('post', bors_lcml::$data['post_functions']);

		bors_lcml::$data['post_whole_functions'] = array();
		bors_lcml::actions_load('post-whole', bors_lcml::$data['post_whole_functions']);

		bors_lcml::actions_load('tags');

		if(config('lcml_sharp_markup'))
			bors_lcml::actions_load('sharp');
	}

	private static function actions_load($rel_dir, &$functions = array())
	{
		foreach(bors_dirs() as $base_dir)
			bors_lcml::_actions_load(secure_path($base_dir.'/engines/lcml/'.$rel_dir), $functions);
	}

	private static function _actions_load($dir, &$functions = array())
	{
//		echo "Load $dir<br/>\n";
        if(!is_dir($dir))
			return;

		$files = self::memcache()->get('lcml_actions_'.@$_SERVER['HTTP_HOST'].'_3:'.$dir);
		if(!$files)
		{
	        $files = array();

    	    if($dh = opendir($dir)) 
        	    while(($file = readdir($dh)) !== false)
            	    if(is_file($dir.'/'.$file))
                	    $files[] = $file;

	        closedir($dh);

			sort($files);
			self::memcache()->set($files);
		}

        foreach($files as $file) 
        {
            if(preg_match("!(.+)\.php$!", $file, $m))
            {
                include_once("$dir/$file");

                $fn = "lcml_".substr($file, 3, -4);

                if(function_exists($fn))
					$functions[] = $fn;
            }
        }
	}

	private function functions_do($functions, $text)
	{
		foreach($functions as $fn)
		{
			$original = $text;

			$text = $fn($text);

			if(!trim($text) && trim($original))
				debug_hidden_log('lcml-error', "Drop on $fn convert '$original'");
		}

		return $text;
	}


	function parse($text, $params = array())
	{
		$text = str_replace("\r", '', $text);
		if(!trim($text))
			return '';

		if($this->_params['level'] == 1 && !config('lcml_cache_disable') && config('cache_engine'))
		{
			$cache = new Cache();
			if($cache->get('lcml-cache', $text))
				return $cache->last();
		}
		else
			$cache = NULL;

		$GLOBALS['lcml']['params'] = $this->_params;
		$GLOBALS['lcml']['params']['html_disable'] = $this->p('html_disable');
		$GLOBALS['lcml']['cr_type'] = $this->p('cr_type');

		if($this->_params['level'] == 1)
			$text = $this->functions_do(bors_lcml::$data['pre_functions'], $text);

		$mask = str_repeat('.', bors_strlen($text));

		$text = lcml_tags($text, $mask);

		if($this->p('only_tags'))
			return $cache ? $cache->set($text, 86400) : $text;

		if(config('lcml_sharp_markup'))
		{
			require_once('engines/lcml/sharp.php');
			$text = lcml_sharp($text, $mask);
		}

		$result = "";
		$start = 0;
		$can_modif = true;

		for($i=0, $stop=bors_strlen($text); $i<$stop; $i++)
		{
			if($mask[$i] == 'X')
			{
				if($can_modif)
				{
					if($start != $i)
						$result .= bors_lcml::functions_do(bors_lcml::$data['post_functions'], bors_substr($text, $start, $i-$start));

					$start = $i;
					$can_modif = false;
				}
			}
			else
			{
				if(!$can_modif)
				{
					$result .= bors_substr($text, $start, $i-$start);
					$start = $i;
					$can_modif = true;
				}
			}
		}

		if($start < bors_strlen($text))
		{
			if($can_modif)
				$result .= bors_lcml::functions_do(bors_lcml::$data['post_functions'], bors_substr($text, $start, bors_strlen($text) - $start));
			else
				$result .= bors_substr($text, $start, bors_strlen($text) - $start);
		}

		$text = $result;

		if($this->_params['level'] == 1)
			$text = $this->functions_do(bors_lcml::$data['post_whole_functions'], $text);

		return $cache ? $cache->set($text, 86400) : $text;
	}
}

function lcml($text, $params = array())
{
	static $lc = false;
	if($lc === false)
		$lc = new bors_lcml($params);

	$lc->set_p('level', $lc->p('level')+1);
	$save_tags = $lc->p('only_tags');
	if(!empty($params['only_tags']))
		$lc->set_p('only_tags', $params['only_tags']);
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

	$text = preg_replace("!<a [^>]*href=\"([^\"]+)\"[^>]*>(.*?)</a>!is", '[url=$1]$2[/url]', $text);
	$text = preg_replace('!(<img ([^>]+)>)!ise', 'lcmlbb_parse_img(stripslashes("$1"));', $text);

	$text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

	$text = preg_replace('!<lj\-embed id="(\d+)" />!ise', "lcmlbb_lj_embed(\"$1\", '$url');", $text);
	$text = str_replace('embed', 'xx', $text);

	$text = preg_replace("/^\s+$/m", '', $text);

	$text = preg_replace("!\n(&nbsp;| )\n!", "\n\n", $text);
	$text = preg_replace("!\n{2,}!", "\n\n", $text);

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
	$dom = new DOMDocument;
	@$dom->loadHTML($tag);
	$x = $dom->getElementsByTagName('img')->item(0);
//	$x = $xs[0];
//	echo "src=".$x->getAttribute('src')."\n";
	return "[img]{$x->getAttribute('src')}[/img]";
}

function url_relative_join($url_main, $url_rel)
{
	if($url_rel[0] == '/')
		return preg_replace('!^(http://[^/]+).+?$!', '$1', $url_main).$url_rel;

	return $url_main . $url_rel;
}
