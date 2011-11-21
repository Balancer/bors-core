<?php

function mkpath($strPath, $mode=0777)
{
    if(!$strPath || is_dir($strPath) || $strPath=='/')
        return true;

	if(!($pStrPath = dirname($strPath)))
		return true;

	if(!mkpath($pStrPath, $mode)) 
        return false;

	$err = @mkdir($strPath, $mode);
	@chmod($strPath, $mode);
	return $err;
}
