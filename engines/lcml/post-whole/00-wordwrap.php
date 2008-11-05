<?
    function lcml_wordwrap($txt)
    {
		return preg_replace('!(\S{100})!',  "$1 ", $txt);
    }
