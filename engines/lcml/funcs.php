<?
    function c_type($txt)
    {
        if(preg_match("/^[0-9]$/",$txt)) return 1;
        if(preg_match("/^[A-Za-z]$/",$txt)) return 2;
        if(preg_match("/^[а-яА-Я]$/u",$txt)) return 3;
        return 0;
    }

    function check_lcml_access($var, $default=false)
    {
        return $default;
    }

    function save_format($txt)
    {
        $txt = str_replace(
			array(" ","\n","<",">","&", "*","#"),
			array("---save_space---","---save_cr---","---less---","---great---","---ampersand---","---mul---","---hash---"),
			$txt);
        return $txt;
    }

    function restore_format($txt)
    {
        $txt = str_replace(
			array("---save_space---","---save_cr---","---less---","---great---","---ampersand---","---mul---","---hash---"), 
			array(" ","\n","<",">","&","*","#"), 
			$txt);
        return $txt;
    }

    function remove_format($txt)
    {
        $txt = preg_replace(array("!\s+!","!\n!"),array(" "," "),$txt);
        return $txt;
    }
