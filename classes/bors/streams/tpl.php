<?php

class bors_streams_tpl
{
	var $file_name_with_path;
	var $fh;

	function stream_open($path, $mode, $options, &$opened_path)
	{
		if(!preg_match('!^tpl:/(/.*)$!', $path, $m))
			bors_throw("Incorrect stream type: '$path'");

		$path = $m[1];

		$found = false;
		foreach(bors_dirs() as $dir)
		{
			if(file_exists($f = "{$dir}/templates{$path}"))
			{
				$found = $f;
				break;
			}

			if(file_exists($f = "{$dir}/templates{$path}/index.html"))
			{
				$found = $f;
				break;
			}
		}

		if(!$found)
			return false;

		$this->file_name_with_path = $found;
		$this->fh = fopen($found, $mode);

        return true;
    }

	function stream_stat()
	{
		return fstat($this->fh);
//		return stat($this->file_name_with_path);
	}

	function stream_read($count) { return fread($this->fh, $count); }

    function stream_write($data)
    {
		echo "stream_write";
		var_dump($data);
		exit();
    }

    function stream_tell()
    {
		echo "stream_tell";
		exit();
    }

    function stream_eof() { return feof($this->fh); }

    function stream_seek($offset, $whence)
    {
		echo "stream_seek($offset, $whence)\n";
		exit();
    }
}
