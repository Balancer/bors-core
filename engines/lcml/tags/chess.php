<?
    function lp_chess($text,$params)
    {
		global $wgScriptPath;
		$errrep_save = error_reporting();
		error_reporting($errrep_save & ~E_NOTICE);
		define( 'MEDIAWIKI', true );
		$incs =  ini_get('include_path');
		include_once("/var/www/wiki.airbase.ru/htdocs/LocalSettings.php");
		$ret = Wikitex::chess($text, array());
		error_reporting($errrep_save);
		ini_set('include_path', $incs);

		if(preg_match("/^!/m", $ret))
			return "<span style=\"font-size: 8pt;\">".save_format($ret)."</span>";
		else
			return save_format($ret);
	}
