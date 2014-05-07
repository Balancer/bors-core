<?php

if(class_exists('Lingua_Stem_Ru'))
	return;

$charset = str_replace('-', '', strtolower(config('internal_charset')));
eval("class Lingua_Stem_Ru extends Lingua_Stem_Ru_$charset { }");
