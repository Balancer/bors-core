<?php

function ungpc(&$array)
{
	$result = array();

	foreach($array as $key => $val)
	{
		if(is_array($val))
			$result[$key] = ungpc($val);
		else
			$result[$key] = stripslashes($val);
	}
	return $array = $result;
}


	function get_new_global_id($db = NULL)
	{
		$db = &new DataBase($db);
		$db->query("UPDATE hts_ext_system_data SET `value`=`value`+1 WHERE `key`='global_id';", false);
		return $db->get("SELECT `value` FROM hts_ext_system_data WHERE `key`='global_id';", false);
	}

	function new_id($engine, $local_id = NULL, $uri = NULL)
	{
		debug_exit("new_id($engine)");
	
		$db = &new DataBase('HTS');

		$local_id	= $local_id	? ", local_id = ".intval($local_id) : "";
		$uri		= $uri 			? ", uri = ".addslashes($uri) : "";

		$db->query("INSERT INTO `global_ids` SET `engine` = '".addslashes($engine)."'$local_id$uri");
		$new_id = intval($db->last_id());
		
		if(!$new_id)
			exit("Ошибка получения global id");
			
		return $new_id;
	}

	function uri_by_global_id($id)
	{
		debug_exit("uri_by_global_id($id)");
		$db = &new DataBase('HTS');

		return $db->get("SELECT uri FROM global_ids WHERE id = ".intval($id));
	}

	function global_id($engine, $local_id, $register=true)
	{
		debug_exit("global_id($engine)");
		$db = &new DataBase('HTS');

		$id = $db->get("SELECT id FROM global_ids WHERE engine='".addslashes($engine)."' AND local_id = ".intval($local_id));

		if($id || !$register)
			return $id;

		return new_id($engine, $local_id);
	}

	function engine_id_by_global($id)
	{
		debug_exit("engine_id($id)");
		$db = &new DataBase('HTS');

		$res = $db->get("SELECT engine, local_id FROM global_ids WHERE id = ".intval($id));
		return array($res['engine'], $res['local_id']);
	}
