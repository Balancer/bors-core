<?php
    require_once("config.php");

    function smarty_function_load_data($params, &$smarty)
    {
//		print_r($params);
	
        $hts = &new DataBaseHTS;
		
		$add = "";
		
		$norm = $hts->normalize_uri($params['page']);
		
        if(empty($params['page']))
            $params['page'] = $GLOBALS['main_uri'];

		$lcml = false;
		if($params['key'] == 'body')
		{
			$params['key'] = 'source';
			$lcml = true;
		}

        $ldp = $params['page'];
		$uri = $hts->normalize_uri($ldp);
        $src = $hts->get_data($uri, $params['key']);
		$add .= $hts->normalize_uri($ldp);

        if(!$src)
		{
			$uri = $hts->normalize_uri($GLOBALS['main_uri']."$ldp/");
            $src = $hts->get_data($uri, $params['key']);
		}
			
		if($lcml)
			$src = lcml($src, array(
				"page"      => $uri,
				"cr_type"   => $hts->get_data($uri, "cr_type"),
				'html' => true,
			));


        return /*"=$add|{$params['key']}=".*/ $src;
    }
