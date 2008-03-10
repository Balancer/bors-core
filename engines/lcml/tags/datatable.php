<?
    function lp_datatable($text, $iparams)
    {
//		exit($GLOBALS['lcml']['params']['uri']);
	
		@list($objs, $params) = @split("\n\n", trim($text));
		$objs = split("\n", $objs);
		
		$db = new DataBase('AIRBASE');
		
		if($params)
			$params = split("\n", $params);
		else
		{
			foreach($objs as $obj)
				$obj_list[] = "'".addslashes($obj)."'";
				
			$obj_list = join(",", $obj_list);
			
			$params = $db->get_array("SELECT DISTINCT `parameter` FROM `OBJECTS` WHERE `object` IN ($obj_list)");
		}

		$p_list = array();
		
		foreach($params as $p)
			$p_list[] = "'".addslashes($p)."'";
				
		if(!$p_list)
			return ec("Объект не найден");
			
		$p_list = join(",", $p_list);

		$out = "<table class=\"btab\" cellSpacing=\"0\">";
		if(!empty($iparams['description']))
			$out .= "<caption>{$iparams['description']}</caption>";

		foreach($db->get_array("
			SELECT DISTINCT 
				oc.name, oc.priority
			FROM `obj_param` op
				LEFT JOIN `OBJECTS_CATEGORIES` oc ON (op.category = oc.name)
			WHERE op.name IN ($p_list)
			ORDER BY oc.priority, oc.name") as $cat)
		{
			$out .= "<tr><th colSpan=\"".(sizeof($objs)+1)."\" style=\"text-align: left;\">{$cat['name']}</th></tr>";
			
			if(sizeof($objs) > 1)
			{
				$out .= "<tr><th>&nbsp;</th>";
				foreach($objs as $o)
					$out .= "<th>$o</th>";
				$out .= "</tr>";
			}
			
			$params = $db->get_array("SELECT DISTINCT `parameter` 
				FROM `OBJECTS` o
					LEFT JOIN obj_param op ON (o.parameter = op.name)
					LEFT JOIN `OBJECTS_CATEGORIES` oc ON (op.category = oc.name AND oc.name = '".addslashes($cat['name'])."')
				WHERE `parameter` IN ($p_list) 
					AND oc.name = '".addslashes($cat['name'])."'
				ORDER BY op.priority");

			foreach($params as $p)
			{
				$out .= "<tr><td><b>$p</b></td>";
				foreach($objs as $o)
				{
					$val = $db->get("SELECT value FROM OBJECTS WHERE object='".addslashes($o)."' AND parameter='".addslashes($p)."'");
					if(!$val)
						$val = "&nbsp;";
					$out .= "<td>$val</td>";
				}
				$out .= "</tr>";
			}
		}

		$out .= "</table>";

		$ch = new Cache();
		foreach($objs as $o)
		{
			$ch->get('datatable-using', rand(), "dbuse://".addslashes($o)."/");
			$ch->set($GLOBALS['lcml']['params']['uri']);
		}
		
		return $out;
	}
?>
