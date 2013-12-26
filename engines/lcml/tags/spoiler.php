<?php

function lp_spoiler($txt,$params)
{
	$id = time().rand();
	$lcml = $params['lcml'];
	$saved_level = $lcml->p('level');
	$lcml->set_p('level', 0);
	$html = lcml($txt);
	$lcml->set_p('level', $saved_level);
//	if(config('is_developer')) { print_dd($html); exit(); }
	return "<a href=\"#\" class=\"spoiler\" onClick=\"var d=(el=(document.getElementById('$id'))).style; if(d.display==''){d.display='none';el.innerHTML=Base64.encode(el.innerHTML);(document.getElementById('_$id')).innerHTML='показать'}else{el.innerHTML=Base64.decode(el.innerHTML);d.display='';(document.getElementById('_$id')).innerHTML='скрыть'}; return false;\">".@$params['description']."&nbsp[<span id=\"_$id\">показать</span>]</a><div id=\"$id\" style=\"display: none; border: 1px dotted; margin: 0px 10px 4px 20px; padding: 2px;\">".base64_encode($html)."</div>\n";
}
