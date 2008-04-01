<?
	define('BORS_CORE', '/home/balancer/work/programming/php/bors-core');
	include_once(BORS_CORE.'/config.php');

	$db = &new DataBase('CACHE');

	foreach($db->get_array("SELECT file FROM cached_files WHERE expire_time BETWEEN 0 AND ".time()) as $file)
	{
		echo "$file<br />\n";
		$db->query("DELETE FROM cached_files WHERE file = '".addslashes($file)."'");
		@unlink($file);
	}
