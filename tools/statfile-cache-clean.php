<?
	define('BORS_CORE', dirname(dirname(__FILE__)));
	define('BORS_LOCAL', dirname(BORS_CORE).'/bors-local');

	include_once(BORS_CORE.'/config.php');

	include_once('obsolete/DataBase.php');

	$db = &new DataBase('CACHE');

	foreach($db->get_array("SELECT file FROM cached_files WHERE expire_time BETWEEN 0 AND ".time()) as $file)
	{
		echo "$file<br />\n";
		$db->query("DELETE FROM cached_files WHERE file = '".addslashes($file)."'");
		//TODO: ввести в БД поле автообновления при удалении.
		@unlink($file);
	}
