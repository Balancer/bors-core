<?php

require_once ('lcml/funcs.php');
require_once ('lcml/extentions.php');
require_once ('inc/urls.php');

//$GLOBALS['cms']['smilies_dir'] = "{$GLOBALS['cms']['main_host_dir']}/forum/smilies";
//$GLOBALS['cms']['smilies_url'] = "{$GLOBALS['cms']['main_host_uri']}/forum/smilies";
//$GLOBALS['cms']['images_dir'] = "{$_SERVER['DOCUMENT_ROOT']}/images";
//    $GLOBALS['cms_images_url']='http://img.airbase.ru';
//$GLOBALS['cms']['sites_store_path'] = "{$GLOBALS['cms']['main_host_dir']}/sites";
//$GLOBALS['cms']['sites_store_url'] = "{$GLOBALS['cms']['main_host_uri']}/sites";

ext_load(dirname(__FILE__).'/lcml/tags');

function lcml_out($txt)
{
	$txt = preg_replace("!(\s)(http://|ftp://)(\S+)(\s)!i", "$1<a href=\"$2$3\">$2$3</a>$4", $txt);
	return $txt;
}

function rest_return($ret_val, $saved_params)
{
	$GLOBALS['lcml']['params'] = $saved_params;
	$GLOBALS['lcml']['level']--;
	return $ret_val;
}

function lcml($txt, $params = array ())
{
	$txt = preg_replace("!^\n+!", "", $txt);
	$txt = preg_replace("!\n+$!", "\n", $txt);

	if(!trim($txt))
		return "";

	$GLOBALS['lcml']['level'] = intval(@ $GLOBALS['lcml']['level']) + 1;

	if($GLOBALS['lcml']['level'] > 20)
	{
		echo "Maximum function nesting level for <xmp>".$txt."</xmp>\n";
		return $txt;
	}
	
	$saved_params = empty ($GLOBALS['lcml']['params']) ? array () : $GLOBALS['lcml']['params'];
	foreach ($saved_params as $key => $val)
		if (!isset ($params[$key]))
			$params[$key] = $val;

	$GLOBALS['lcml']['params'] = &$params;
	
	if($GLOBALS['lcml']['level'] > 1)
		$GLOBALS['lcml']['params']['html_disable'] = false;

	if (!trim($txt))
		return rest_return($txt, $saved_params);

	if(empty($params['uri']))
		$params['uri'] = '';

	$ch_type = "lcml-compiled";
	$ch_key = md5($txt.$params['uri']);

	$ch = &new Cache();

	if($ch->get($ch_type, $ch_key, $params['uri'])
				&& empty ($params['cache_disable'])
				&& !config('cache_disabled')
				&& $GLOBALS['lcml']['level'] < 2
			)
		return rest_return($ch->last(), $saved_params);

	$page = @ $GLOBALS['cms']['page_path'];

//	$hts = &new DataBaseHTS();

	$data = url_parse($page);

	if (empty ($params['page_path']))
		$params['page_path'] = $data['path'];

	if (empty ($params['uri']))
		$params['uri'] = $page;

	$outfile = 0;

	if ($outfile)
	{
		$fh = fopen($GLOBALS['cms']['base_dir']."/funcs/lcml.log", "at");
		fwrite($fh, $txt."\n---------------------------------------------\n");
		fclose($fh);
	}

	if (is_array($params))
	{
		foreach ($params as $key => $value)
		{
			//				if(user_data('level')>100)
			//					$txt .= "$key = {$value}<br/>";
			$GLOBALS['lcml'][$key] = $value;
		}
	}
	else
	{
		debug(__FILE__.__LINE__." Unknown parameter '$params'");
	}

	if(empty ($GLOBALS['lcml']['cr_type']))
		$GLOBALS['lcml']['cr_type'] = 'empty_as_para';

	if ($GLOBALS['lcml']['cr_type'] == 'plain_text')
		return rest_return($ch->set("<xmp>$txt</xmp>", 86400*3), $saved_params);

	if (empty ($page))
		$page = '';

	$page = empty ($GLOBALS['lcml']['page']) ? $page : $GLOBALS['lcml']['page'];

	//        if($page) include("config.php");

	$txt = str_replace("\r", "", $txt);

	//        require_once("tags/code.php");
	//        $txt=preg_replace("!\[code([^\]]*)\](.+?)\[/code\]!ise","lp_code_(\"$2\",'$1')",$txt);

	$txt = ext_load(dirname(__FILE__).'/lcml/pre', $txt);

	$mask = str_repeat('.', strlen($txt));

	if(!config('lcml_sharp_markup_skip'))
	{
		include_once ('lcml/sharp.php');
		$txt = lcml_sharp($txt, $mask);
	}
	
	include_once ("lcml/tags.php");
	$txt = lcml_tags($txt, $mask);

	$txt = ext_load(dirname(__FILE__).'/lcml/post', $txt, $mask);

	if($GLOBALS['lcml']['level'] == 1)
		$txt = ext_load(dirname(__FILE__).'/lcml/post-whole', $txt);

	if ($outfile)
	{
		$fh = fopen($GLOBALS['cms']['base_dir']."/funcs/lcml.log", "at");
		fwrite($fh, $txt."\n=============================================\n\n");
		fclose($fh);
	}

	$m = array ();
	if (preg_match("!^(#.+)$!m", $txt, $m) && !empty ($GLOBALS['lcml']['page']))
		debug("{$GLOBALS['lcml']['page']}: {$m[1]}", "LCML:");
	if (preg_match("!(\[.+?\])!m", $txt, $m) && !empty ($GLOBALS['lcml']['page']))
		debug("{$GLOBALS['lcml']['page']}: {$m[1]}", "LCML:");

	//        if(user_data('member_id') == 1)
	//            xdebug_dump_function_profile(XDEBUG_PROFILER_CPU); 

	//		echo "<xmp>Out: '$txt'</xmp>";

	return rest_return($ch->set($txt, 86400*14), $saved_params);

}

function lcmlbb($string)
{
	$ch = &new Cache();
	if($ch->get('smarty-modifiers-lcmlbb-compiled', $string))
		return $ch->last();

	return $ch->set(lcml($string, 
		array(
			'cr_type' => 'save_cr',
			'forum_type' => 'punbb',
//			'forum_base_uri' => 'http://balancer.ru/forum',
			'sharp_not_comment' => true,
			'html_disable' => false,
//			'uri' => "post://{$cur_post['id']}/",
	)), 7*86400);
}
