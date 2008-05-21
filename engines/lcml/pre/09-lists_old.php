<?
    function lcml_lists_old($txt)
    {
//		echo preg_replace("!^(\*+) [^\*]+!me", "str_repeat(' ', strlen(\"$1\")).'* '", $txt);
		return preg_replace("!^(\*+) ([^*]+)!me", "str_repeat(' ', strlen(\"$1\")).'* $2'", $txt);
    }
