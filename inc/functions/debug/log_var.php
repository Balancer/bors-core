<?php

function debug_log_var($var, $value) { return $GLOBALS['bors_debug_log_vars'][$var] = $value; }
