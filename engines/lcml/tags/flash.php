<?
    function lp_flash($url,$params)
    {
        list($width,$height)=split("x",(isset($params['size'])?$params['size']:"")."x");
        if(!$width)  $width=468;
        if(!$height) $height=351;
        return "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=$width height=$height><param name=movie value=$url><param name=play value=true><param name=loop value=true><param name=quality value=high><embed src=$url width=$width height=$height play=true loop=true quality=high></embed></object>";
    }

	function lt_param($params)
	{
		if(preg_match('!&amp;!', $params['value']))
			$params['value'] = html_entity_decode($params['value']);
		return "<param ".make_enabled_params($params, 'name value')." />";
	}

	function lp_embed($inner, $params)
	{
		return "<embed ".make_enabled_params($params, 'src type wmode width height scale salign allowfullscreen allowsriptaccess flashvars').">".lcml($inner)."</embed>";
	}

	function lp_object($inner, $params)
	{
		if(preg_match('!&amp;!', $params['codebase']))
			$params['codebase'] = html_entity_decode(@$params['codebase']);
		return "<object ".make_enabled_params($params, 'codebase data width height type').">".lcml($inner)."</object>";
	}

	function lp_td($inner, $params)
	{
		return "<td ".make_enabled_params($params, 'class style').">".lcml($inner)."</td>";
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
		return "<tr ".make_enabled_params($params, 'class style').">".lcml($inner)."</tr>";
	}

	function lp_html_a($inner, $params)
	{
		$params['href'] = preg_replace("!javascript!", "жабаскрипт", $params['href']);
		if(preg_match('!&amp;!', $params['href']))
			$params['href'] = html_entity_decode($params['href']);
		return "<a ".make_enabled_params($params, 'href style title').">".lcml($inner)."</a>";
	}

	function lt_html_img($params)
	{
		$params['src'] = preg_replace("!javascript!", "жабаскрипт", $params['src']);
		return "<img ".make_enabled_params($params, 'src align style width height')." />";
	}
