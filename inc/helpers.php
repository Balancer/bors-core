<?php

require BORS_CORE.'/classes/bors/class/loader.php';
function class_include($class_name, &$args = array()) { return bors_class_loader::load($class_name, $args); }
