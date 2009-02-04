<?php

    function lcml_restore_format($txt)
    {

//		$txt = preg_replace('/&amp;(\w+;)/e', "html_entity_decode(\"&$1\")", $txt);

        return restore_format($txt);
    }
