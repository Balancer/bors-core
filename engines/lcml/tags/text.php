<?
    function lp_h($text) { return " <h2> $text </h2> "; }

	function lp_term($text) { return save_format("<tt class=\"code\">".restore_format($text)."</tt>"); }
	function lp_cterm($text) { return "<tt class=\"code\">".lcml($text)."</tt>"; }

	function lp_lcml($text) { return lcml($text); }
