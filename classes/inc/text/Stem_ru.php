<?php

if(class_exists('Lingua_Stem_Ru'))
	return;

$charset = str_replace('-', '', strtolower(config('internal_charset')));
require_once(__DIR__.'/Stem_ru-'.$charset.'.php');
eval("class Lingua_Stem_Ru extends Lingua_Stem_Ru_$charset { }");
