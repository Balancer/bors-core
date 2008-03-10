<?
    function lp_size($txt,$params)
    {
		$params['orig'] = trim($params['orig']);
		if("".intval($params['orig']) == $params['orig'])
			$params['orig'] .= "pt";
        return "<div style=\"font-size: ".addslashes($params['orig']).";\">".lcml($txt)."</div>";
    }
?>
