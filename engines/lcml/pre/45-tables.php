<?php

function lcml_tables($txt)
{
	if(!preg_match("!^#i?table!m", $txt))
		return $txt;

	$file = "/tmp/lcml_tables_".time().rand();
	$fh = fopen($file, "wb");
	fwrite($fh, $txt);
	fclose($fh);

	$res = `/var/www/bors/bors-airbase/tools/cgi-bin/table.cgi $file`;
	if(trim($res))
	{
		$txt = $res;
		unlink($file);
	}
	else
		echo "Table error: $file";

	return $txt;
}
