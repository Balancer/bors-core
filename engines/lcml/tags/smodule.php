<?
    function lt_smodule($params)
    { 
        $ps = "";
		foreach($params as $key=>$value)
			$ps.="\$GLOBALS['module_data']['$key'] = '".addslashes($value)."';\n";

        return save_format("{php}
$ps
include(\"modules/{$params['url']}.php\");
{/php}");
    }
?>