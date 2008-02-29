<?
    function smarty_modifier_regex_match($string, $test)
    {
        return preg_match("!$test!", $string);
    }
?>