<?
    function lp_chem($text,$params)
    {
		global $wgScriptPath;
		$errrep_save = error_reporting();
		error_reporting($errrep_save & ~E_NOTICE);
		define( 'MEDIAWIKI', true );
		$incs =  ini_get('include_path');
		include_once("/var/www/wiki.airbase.ru/htdocs/LocalSettings.php");
		$ret = Wikitex::chem($text, array());
		error_reporting($errrep_save);
		ini_set('include_path', $incs);
		return $ret;
	}
?>
