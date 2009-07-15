<?php
    function lcml_uka_url_fix($txt)
    {
		$txt = str_replace('airbase.uka.ru', 'airbase.ru', $txt);
		
        return $txt;
    }
