<?php

function lp_code($txt, $params)
{
	$txt = restore_format($txt);
	$txt = html_entity_decode($txt, ENT_NOQUOTES);

	$params['language'] = $params['url'];

	foreach(explode(' ', config('lcml.code.engines_order')) as $code_engine_class_name)
	{
		$highliter = object_load($code_engine_class_name);
		if($res = $highliter->render($txt, $params))
			return save_format($res);
	}

	include_once("funcs/modules/colorer.php");

	$txt = str_replace("lcml_save_left_bracket", "[", $txt);
	$txt = str_replace("lcml_save_lt", "<", $txt);
	$txt = str_replace("lcml_save_gt", ">", $txt);

	$txt = colorer($txt, $params['orig']);

	$txt = preg_replace("! +$!", "", $txt);

	if(preg_match("!(Created with colorer.+?Type ')(.+?)(')$!m", $txt, $m))
		$txt = preg_replace("!(Created with colorer.+?Type ')(.+?)(')$!m", "", $txt);

        $txt=preg_replace("!^\n+!","",$txt);
        $txt=preg_replace("!\n+$!","",$txt);

        $txt=explode("\n", $txt);
        foreach($txt as $s)
            $s=" $s";
        
		$txt=join("\n",$txt);

//		$txt = str_replace("\n", "<br />---save_cr---", $txt);
		$txt = str_replace("\n", "<br />\n", $txt);

        $txt = "<div class=\"code\"><tt>$txt</tt>";

        if(isset($m[2]))
            $txt.="<div class=\"code_type\">code, type '<b>{$m[2]}</b>'</div>";

		$txt .= "</div>";
        
//        $txt = preg_replace("!( {2,})!em","str_repeat('&nbsp;',strlen('$1'))", $txt);
		$txt = preg_replace("!^ !m","&nbsp;",$txt);
		$txt = preg_replace("! {2}!","&nbsp; ",$txt);
		
		return save_format($txt);
}
