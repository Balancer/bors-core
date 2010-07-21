<?php
    function smarty_modifier_strftime($time, $mask)
    {
		if($time == 0)
			$time = $GLOBALS['now'];
	
        if(!preg_match("!^\d+$!", $time))
            return $time;

        return strftime($mask, $time);
    }
?>