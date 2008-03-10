<?
    function lcml_lists_old($txt)
    {
		return preg_replace("!^(\*+) !me", "str_repeat(' ', strlen(\"$1\")).'* '", $txt);
    }
