<?
    function smarty_modifier_strftime($time, $mask)
    {
		if($time == 0)
			$time = time();
	
        if(!preg_match("!^\d+$!", $time))
            return $time;

        return strftime($mask, $time);
    }
?>