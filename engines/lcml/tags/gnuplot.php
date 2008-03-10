<?
    function lp_gnuplot($text,$params)
    {
		global $wgScriptPath, $wgUploadPath, $wgUploadDirectory, $wgGnuplotSettings;
		$errrep_save = error_reporting();
		error_reporting($errrep_save & ~E_NOTICE);
		define( 'MEDIAWIKI', true );
		$incs =  ini_get('include_path');
		include_once("/var/www/wiki.airbase.ru/htdocs/LocalSettings.php");
		include_once('/var/www/wiki.airbase.ru/htdocs/includes/GlobalFunctions.php');
		include_once('/var/www/wiki.airbase.ru/htdocs/extensions/Gnuplot.php');
		$ret = renderGnuplot($text);
		error_reporting($errrep_save);
		ini_set('include_path', $incs);
		return $ret;
	}
