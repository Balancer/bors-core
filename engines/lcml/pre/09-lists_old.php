<?php
    function lcml_lists_old($txt)
    {
//		echo preg_replace("!^(\*+) [^\*]+!me", "str_repeat(' ', strlen(\"$1\")).'* '", $txt);
		return preg_replace_callback("!^(\*+) ([^*]+)!m", function($m) { return str_repeat(' ', strlen($m[1])).stripq('* '.$m[2]);}, $txt);
    }
