<?
    function lp_flash($url,$params)
    {
        list($width,$height)=split("x",(isset($params['size'])?$params['size']:"")."x");
        if(!$width)  $width=468;
        if(!$height) $height=351;
        return "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=$width height=$height><param name=movie value=$url><param name=play value=true><param name=loop value=true><param name=quality value=high><embed src=$url width=$width height=$height play=true loop=true quality=high></embed></object>";
    }

	function make_enabled_params($params, $names_list)
	{
		$res = array();
		foreach(split(' ', $names_list) as $name)
			if(!empty($params[$name]))
				$res[] = "$name=\"".$params[$name]."\"";
		return join(' ', $res);
	}

	function lt_param($params)
	{
		return "<param ".make_enabled_params($params, 'name value')." />";
	}

	function lp_embed($inner, $params)
	{
		return "<embed ".make_enabled_params($params, 'src type wmode width height scale salign allowFullScreen allowSriptAccess').">".lcml($inner)."</embed>";
	}

	function lp_object($inner, $params)
	{
		return "<object ".make_enabled_params($params, 'width height').">".lcml($inner)."</object>";
	}

	function lp_style($inner, $params)
	{
		return "<style ".make_enabled_params($params, 'type').">$inner</style>";
	}

	function lp_td($inner, $params)
	{
		return "<td ".make_enabled_params($params, 'style').">".lcml($inner)."</td>";
	}

	function lp_span($inner, $params)
	{
		return "<span ".make_enabled_params($params, 'style').">".lcml($inner)."</span>";
	}


	function lp_div($inner, $params)
	{
		return "<div ".make_enabled_params($params, 'style').">".lcml($inner)."</div>";
	}

	function lp_tabtr($inner, $params)
	{
		return "<tr>".lcml($inner)."</tr>";
	}

	function lp_table($inner, $params)
	{
		return "<table ".make_enabled_params($params, 'style border').">".lcml($inner)."</table>";
	}

	function lp_html_a($inner, $params)
	{
		$params['href'] = preg_replace("!javascript!", "жабаскрипт", $params['href']);
		return "<a ".make_enabled_params($params, 'href style').">".lcml($inner)."</a>";
	}

	function lt_html_img($params)
	{
		$params['src'] = preg_replace("!javascript!", "жабаскрипт", $params['src']);
		return "<img ".make_enabled_params($params, 'src align style')." />";
	}
