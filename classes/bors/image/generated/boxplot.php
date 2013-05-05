<?php

require_once(config('boxplot_include'));

debug_hidden_log('___catch', 'image generated');

class bors_image_generated_boxplot extends base_image
{
	private $_hash, $_width, $_height, $_data, $_bp;

	function __construct($id)
	{
		$this->_hash = md5(serialize($id));
		$this->_width = defval($id, 'width', 500);
		$this->_height = defval($id, 'height', 500);
		$this->_data = defval($id, 'data');
		$this->_bp = new boxPlotExtend($this->_data);
		$this->_bp->setWrite(true);	// if (true) put the label at graphic 
							// if (false) does not put the label

		$this->_bp->setDrawSize($this->_width, $this->_height); // (width, height) of graphic
	}

	function image()
	{
		ob_start();
		$this->_bp->draw();
		$image = ob_get_contents();
		ob_clean();

		return $image;
	}

	function description()
	{
		return $this->_bp->getDescricao();
	}

	function base_name() { return $this->_hash.'.png'; }
	function url($page=NULL) { return '/c/g/'.$this->base_name(); }
	function dir() { return $_SERVER['DOCUMENT_ROOT'].'/c/g'; }
	function width() { return $this->_width; }
	function height() { return $this->_height; }
	function file_path() { return $this->dir().'/'.$this->base_name(); }
}
