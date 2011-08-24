<?php
    function lp_font($txt,$params)
    {
        return "<div style=\"font-family: ".addslashes(trim($params['orig'])).";\">".lcml($txt)."</div>";
    }

	function lp_html_font($text, $params)
	{
		return "<font ".make_enabled_params($params, 'size color face').">".lcml($text)."</font>";
	}

// lp_h1, lp_l2, lp_h3
for($i=1; $i<=3; $i++)
	eval("function lp_h{$i}(\$text, &\$params) { return bors_lcml_tags_pairs_h{$i}::html(\$text,\$params); }");

// lp_h4, lp_l5, lp_h6...
for($i=4; $i<=6; $i++)
	eval("function lp_h{$i}(\$text, &\$params) { \$params['skip_around_cr'] = true; return \"\n\n<h{$i} class=\\\"html\\\">\".lcml(\$text).\"</h{$i}>\n\"; }");
