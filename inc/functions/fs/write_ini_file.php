<?php

function write_ini_file($file, $array, $has_sections=false)
{
	if($has_sections)
	{
		$content = "";
		foreach($array as $section => $values)
		{
			if($content)
				$content .= "\n";

			$content .= "[".$key."]\n";
			$content .= __wif_make_section($values);
		}
	}
	else
		$content = __wif_make_section($array);

	bors_use('fs/file_put_contents_lock');
	return file_put_contents_lock($file, $content);
}

function __wif_make_q($key, $value)
{
	if(is_null($value))
		return "$key =\n";
	if(is_numeric($value))
		return "$key = $value\n";
	if(preg_match('/^\w+$/', $value))
		return "$key = $value\n";

	return "$key = \"".addslashes($value)."\"\n";
}

function __wif_make_section($array)
{
	$content = array();
	foreach($array as $key => $value)
	{
		if(is_array($value))
		{
			for($i=0; $i<count($value); $i++)
				$content[] = __wif_make_q("{$key}[]",  $value[$i]);
		}
		else
			$content[] = __wif_make_q("{$key}",  $value);
	}

	return join("\n", $content);
}
