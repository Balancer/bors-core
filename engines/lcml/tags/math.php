<?php
function lp_math($text,$params)
{
	$text = restore_format($text);

	$width  = defval($params, 'width');
	$height = defval($params, 'height');

	if($width && $height)
	{
		$wxh = "&chs={$width}x{$height}";
		$wxhs = " width=\"{$width}\" height=\"{$height}\"";
	}
	else
	{
		$wxh = '';
		$wxhs = '';
	}

	$image_link = "http://chart.apis.google.com/chart?cht=tx{$wxh}&chl=".urlencode($text);
	return "<img src=\"{$image_link}\"${wxhs} />";


	global $wgScriptPath;
	$errrep_save = error_reporting();
	error_reporting($errrep_save & ~E_NOTICE);
	define( 'MEDIAWIKI', true );
	$incs =  ini_get('include_path');
	include_once("/var/www/wiki.airbase.ru/htdocs/LocalSettings.php");
	$ret = Wikitex::amsmath($text, array());
	error_reporting($errrep_save);
	ini_set('include_path', $incs);

	if(preg_match("/^!/m", $ret))
		return save_format("<span style=\"font-size: 8pt;\">{$ret}</span>");
	else
		return save_format($ret);
}
