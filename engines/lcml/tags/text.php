<?php

function lp_h($text) { return " <h2> ".lcml($text)." </h2> "; }

function lp_term($text) { return "<tt class=\"code\">".htmlspecialchars(restore_format($text))."</tt>"; }
function lp_cterm($text) { return "<tt class=\"code\">".lcml($text)."</tt>"; }

function lp_lcml($text) { return lcml($text); }

function lt_clear($params) { return "<div class=\"clear\">&nbsp;</div>"; }
