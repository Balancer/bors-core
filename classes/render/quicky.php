<?php

class render_quicky extends base_null
{
	function render($object)
	{
		if(!$object->loaded() && !$object->can_be_empty())
			return false;
			
		require_once('quicky/Quicky.class.php');
		$tpl = new Quicky;
		$tpl->compiler_prefs['interpret_varname_params'] = true;
		$tpl->template_dir = dirname($object->class_file()).'/';

		foreach($object->local_template_data_array() as $var => $value)
			$tpl->assign($var, $value);

		foreach($object->local_template_data_set() as $var => $value)
			$tpl->assign($var, $value);

		$tpl->compile_dir = config('cache_dir').'/smarty-templates_c/';
//		$tpl->plugins_dir = array();
//		foreach(bors_dirs() as $dir)
//			$tpl->plugins_dir[] = $dir.'/engines/smarty/plugins';

		$tpl->plugins_dir[] = 'plugins';
		$tpl->cache_dir   = config('cache_dir').'/smarty-cache/';

		return $tpl->fetch($object->template());
	}
}

class xfile_wrapper
{
	public $position = 0;
	public $body;
	private $_file = NULL;
	
	function file($path)
	{
		if($this->_file !== NULL)
			return $this->_file;
	
		$path = preg_replace('!^xfile://!', '', $path);
		
		foreach(bors_dirs() as $dir)
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

stream_wrapper_register('xfile','xfile_wrapper') or die('Failed to register protocol xfile');
