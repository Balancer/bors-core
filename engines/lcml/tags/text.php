<?
    function lp_h($text) { return " <h2> $text </h2> "; }

	function lp_term($text) { return "<tt class=\"code\">$text</tt>"; }
	function lp_cterm($text) { return "<tt class=\"code\">".lcml($text)."</tt>"; }

	function lp_lcml($text) { return lcml($text); }
