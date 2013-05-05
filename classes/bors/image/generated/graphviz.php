<?php

class bors_image_generated_graphviz extends base_image
{
	private $_hash, $_width, $_height, $_data, $_gv;

	function __construct($id)
	{
		$this->_hash = md5(serialize($id));
		$this->_width = defval($id, 'width', 700);
		$this->_height = defval($id, 'height', 800);
		$this->_data = defval($id, 'data');
	}

	function image()
	{
//		echo $this->_data."<br/>\n";
		$fname_src = tempnam('/tmp', 'lcml-graphviz-src');
		$fname_image = $this->file_path();
		file_put_contents($fname_src, $this->_data);
//		echo " /usr/bin/dot -Tpng -o{$fname_image} {$fname_src}";
		system("/usr/bin/dot -Tpng -o{$fname_image} {$fname_src}");
		unlink($fname_src);
		chmod($fname_image, 0666);
//		ob_start();
//		ob_clean();

		return file_get_contents($fname_image);
	}

	function base_name() { return $this->_hash.'.png'; }
	function url($page=NULL) { return '/c/g/'.$this->base_name(); }
	function dir() { return $_SERVER['DOCUMENT_ROOT'].'/c/g'; }
	function width() { return $this->_width; }
	function height() { return $this->_height; }
	function file_path() { return $this->dir().'/'.$this->base_name(); }
}
