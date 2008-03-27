<?php
    function lp_font($txt,$params)
    {
        return "<div style=\"font-family: ".addslashes(trim($params['orig'])).";\">".lcml($txt)."</div>";
    }

	function lp_html_font($text, $params)
	{
		return "<font ".make_enabled_params($params, 'size color face').">".lcml($text)."</font>";
	}
