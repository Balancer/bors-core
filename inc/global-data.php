<?
    $global_key_count_hit=0;
    $global_key_count_miss=0;

    function global_key($type,$key)
    {
        // echo "Get key $type($key)= ... {$GLOBALS['HTS'][$type][$key]}";
        return @$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)] ? $GLOBALS['HTS'][md5($type)][md5($key)] : false;
    }

    function is_global_key($type,$key)
    {
        // echo "Check for key $type($key)= ... ";
        if(@$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)])
        {
            if(empty($GLOBALS['global_key_count_hit']))
                $GLOBALS['global_key_count_hit'] = 0;
            $GLOBALS['global_key_count_hit']++;
            return true;
        }
        else
        {
            if(empty($GLOBALS['global_key_count_miss']))
                $GLOBALS['global_key_count_miss'] = 0;
            $GLOBALS['global_key_count_miss']++;
            return false;
        }
    }

    function set_global_key($type,$key,$value)
    {
		// echo "set ({$this->last_type},{$this->last_key})=$value;<br>"; 
		$GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)] = true;
        return $GLOBALS['HTS'][md5($type)][md5($key)] = $value;
    }

    function clear_global_key($type,$key)
    {
        // echo "set ({$this->last_type},{$this->last_key})=$value;<br>"; 
		unset($GLOBALS['bors_data']['global']['present'][md5($type)][md5($key)]);
		if(!empty($GLOBALS['HTS'][md5($type)][md5($key)]))
	        unset($GLOBALS['HTS'][md5($type)][md5($key)]);
    }
