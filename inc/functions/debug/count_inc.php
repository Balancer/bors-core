<?php

$GLOBALS['bors_debug_counts'] = array();
function debug_count_inc($category, $inc = 1) { @$GLOBALS['bors_debug_counts'][$category] += $inc; }
