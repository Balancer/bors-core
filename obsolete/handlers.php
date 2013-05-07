<?php

function main_handlers_engine($uri)
{
    @header("X-Bors-obsolete: $uri");
//	@header("X-QS: ".str_replace("\n", ' ', print_r($_GET, true)));
    require_once("funcs/handlers.php");

	if(empty($GLOBALS['cms']['only_load']))
	{
		$_SERVER['HTTP_HOST'] = str_replace(':80', '', $_SERVER['HTTP_HOST']);

   		$_SERVER['REQUEST_URI'] = preg_replace("!^(.+?)\?.*?$!", "$1", $_SERVER['REQUEST_URI']);
	}

	$GLOBALS['cms']['page_number'] = 1;

	if(empty($GLOBALS['main_uri']))
		$GLOBALS['main_uri'] = $uri;

	$GLOBALS['cms']['page_path'] = $GLOBALS['main_uri'];

	$GLOBALS['ref'] = @$_SERVER['HTTP_REFERER'];

	if(empty($GLOBALS['cms']['disable']['log_session']))
	{
		include_once("funcs/logs.php");
		log_session_update();
	}
	
	include_once("funcs/handlers.php");

	$GLOBALS['cms_patterns'] = array();
	$GLOBALS['cms_actions']  = array();

	handlers_load();

	if(!empty($GLOBALS['cms']['only_load']))
		return;
		
	return handlers_exec();
}
