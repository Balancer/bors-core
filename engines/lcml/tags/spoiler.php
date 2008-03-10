<?php
    function lp_spoiler($txt,$params)
    { 
        $id = time().rand();
        return "<a href=\"#\" class=\"spoiler\" onClick=\"var d=(document.getElementById('$id')).style; if(d.display==''){d.display='none';(document.getElementById('_$id')).innerHTML='показать'}else{d.display='';(document.getElementById('_$id')).innerHTML='скрыть'}; return false;\">{$params['description']}&nbsp[<span id=\"_$id\">показать</span>]</a><div id=\"$id\" style=\"display: none; border: 1px dotted; margin: 0px 10px 4px 20px; padding: 2px;\">".lcml($txt)."</div>\n";
    }
