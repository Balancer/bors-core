<?php
function smarty_outputfilter_php_include($output, &$smarty)
{
    return preg_replace('!\[php_include\](.+?)\[/php_include\]!e',"smarty_outputfilter_php_include_out('$1')", $output);
}

function smarty_outputfilter_php_include_out($file)
{
    ob_start();
    include("/home/airbase/html/$file");
    $out = ob_get_contents();
    ob_clean();
    return $out;
}

?>
