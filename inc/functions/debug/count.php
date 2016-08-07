<?php

require_once BORS_CORE.'/inc/functions/debug/count_inc.php';

function debug_count($category) { return @$GLOBALS['bors_debug_counts'][$category]; }
