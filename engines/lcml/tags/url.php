<?
	require_once('funcs/DataBaseHTS.php');

	function lt_url($params) 
	{ 
		$url = $params['url'];

		if(preg_match("!^[^/]+\.\w{2,3}!",$url))
			if(!preg_match("!^\w+://!",$url))
				$params['url']="http://$url";

		$hts = NULL;
		if(class_exists('DataBaseHTS'))
			$hts = &new DataBaseHTS();

		if(!preg_match("!^\w+://!",$url) && !preg_match("!^/!",$url))
			$url = @$GLOBALS['main_uri'].$url;

		if($hts)
			$parse = $hts->parse_uri($url);

		$external = $parse['local'] ? '' : ' class="external"';

//		debug("'External' for $url='$external'; parse=".print_r($parse,true));

		if(!$hts->get_data($url, 'create_time') && !$hts->get_data($url, 'title'))
		{
			$hts->set_data($url, 'title', $params['description']);
			$hts->set_data($url, 'modify_time', time());
		}

		if(!isset($params['description']) || $url == $params['description'])
			$params['description'] = $url;
		else
		{
			$description = lcml($params['description'],  array('html'=>'safe'));
//			if(!preg_match('!a href!', $description))
				$params['description'] = $description;
		}
		
		return "<a href=\"$url\"$external>{$params['description']}</a>";
	}
