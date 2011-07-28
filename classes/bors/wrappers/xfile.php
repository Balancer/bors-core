<?php

class bors_wrappers_xfile
{
	public $position = 0;
	public $body;
	private $_file = NULL;

	function file($path)
	{
		if($this->_file !== NULL)
			return $this->_file;

		$path = preg_replace('!^xfile:////!', '', $path); // Убираем самодеятельность Smarty3.

//		echo debug_trace();
//		exit("$path<Br/>\n");
		$path = preg_replace('!^xfile://!', '', $path);

		if($path[0] == '/')
			return $path;

		foreach(bors_dirs(true) as $dir)
			if(file_exists($file = "$dir/templates/$path"))
				return $this->_file = $file;

		return NULL;
	}

	function fetch_template($file)
	{	// Fetch body
		$this->body = file_get_contents($file);
		return true;
	}

	function stream_open($path, $mode, $options, &$opened_path)
	{
		return $this->fetch_template($this->file($path));
	}

	function stream_read($count)
	{
		$ret = substr($this->body,$this->position,$count);
		$this->position += strlen($ret);
		return $ret;
	}

	function stream_write($data) {return;}
	function stream_tell() {return $this->position;}
	function stream_eof() {return $this->position >= strlen($this->body);}
	function stream_seek($offset,$whence) {return;}
	function stream_stat() { return stat($this->_file); }
	function url_stat($path) { return stat($this->file($path)); }
}
