<?php

function lp_php($txt,$params)
    {
    	return $txt; // Блокировано в усмерть.
//        if(!check_lcml_access('usephp', true))
            return $txt;

//		if(user_data('level')<4)
			return $txt;

        $txt = save_format($txt);
        return "<?php $txt ?>";
    }
