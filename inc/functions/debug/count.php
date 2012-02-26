<?php

bors_function_include('debug/count_inc');

function debug_count($category) { return @$GLOBALS['bors_debug_counts'][$category]; }
