<?php
    function lp_font($txt,$params)
    {
        return "<div style=\"font-family: ".addslashes(trim($params['orig'])).";\">".lcml($txt)."</div>";
    }

	function lp_html_font($text, $params)
	{
		return "<font ".make_enabled_params($params, 'size color face').">".lcml($text)."</font>";
	}

function lp_h1($text, $params) { return "<h1 class=\"html\">".lcml($text)."</h1>"; }
function lp_h2($text, $params) { return "<h2 class=\"html\">".lcml($text)."</h2>"; }
function lp_h3($text, $params) { return "<h3 class=\"html\">".lcml($text)."</h3>"; }
function lp_h4($text, $params) { return "<h4 class=\"html\">".lcml($text)."</h4>"; }
