<?php

class bors_streams_bors
{
	var $file_name_with_path;
	var $fh;

	function stream_open($path, $mode, $options, &$opened_path)
	{
		if(!preg_match('!^bors:/(/.*)$!', $path, $m))
			bors_throw("Incorrect stream type: '$path'");

		$path = $m[1];
		$found = false;
		foreach(bors_dirs() as $dir)
		{
			if(file_exists($f = "{$dir}{$path}"))
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

    function stream_eof() { return feof($this->fh); }

/*
    function stream_write($data)
    {
        $left = substr($GLOBALS[$this->varname], 0, $this->position);
        $right = substr($GLOBALS[$this->varname], $this->position + strlen($data));
        $GLOBALS[$this->varname] = $left . $data . $right;
        $this->position += strlen($data);
        return strlen($data);
    }

    function stream_tell()
    {
        return $this->position;
    }

    function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($GLOBALS[$this->varname]) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_END:
                if (strlen($GLOBALS[$this->varname]) + $offset >= 0) {
                     $this->position = strlen($GLOBALS[$this->varname]) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            default:
                return false;
        }
    }
*/
}
