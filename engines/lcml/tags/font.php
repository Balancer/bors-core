<?php
    function lp_font($txt,$params)
    {
        return "<div style=\"font-family: ".addslashes(trim($params['orig'])).";\">".lcml($txt)."</div>";
    }

	function lp_html_font($text, $params)
	{
		return "<font ".make_enabled_params($params, 'size color face').">".lcml($text)."</font>";
	}

for($i=1; $i<=6; $i++)
	eval("function lp_h{$i}(\$text, &\$params) { \$params['skip_around_cr'] = true; return \"\n<h{$i} class=\\\"html\\\">\".lcml(\$text).\"</h{$i}>\n\"; }");
