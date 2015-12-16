<?php

class bors_image_thumbnails_byurl extends bors_object
{
	function data_load()
	{
		$geometry = $this->arg('geometry');
//		var_dump($geometry, $this->arg('origin_url'));

		if(preg_match('!^(\d*)x(\d*)$!', $geometry, $m))
		{
			$this->set_attr('width',  $m[1]);
			$this->set_attr('height', $m[2]);
			$this->set_attr('opts',   NULL);
		}
		elseif(preg_match('!^(\d*)x(\d*)\(([^)]+)\)$!', $geometry, $m))
		{
			$this->set_attr('width',  $m[1]);
			$this->set_attr('height', $m[2]);
			$this->set_attr('opts',   $m[3]);
		}
		else
			return $this->set_is_loaded(false);

		if(!$this->opts())
			return $this->set_is_loaded(false);

		if(!preg_match('!^(http://[^/]+)(/.+?)([^/]+\.(jpe?g|png|gif))$!i', $this->arg('origin_url')))
			return $this->set_is_loaded(false);

		return $this->set_is_loaded(true);
	}

	function wxh($use_alt_title = true)
	{
		$w = "width=\"{$this->width()}\"";
		$h = "height=\"{$this->height()}\"";

//		if($use_alt_title)
//			$alt = "alt=\"[image]\" title=\"".htmlspecialchars($this->alt_or_description())."\"";
//		else
			$alt = "alt=\"\"";

		return  "{$h} {$w} $alt";
	}

	function html($args = array()) { return $this->html_code(@$args['append']); }

	function url()
	{
		$origin_url = $this->arg('origin_url');
		$thumb_url = preg_replace('!^(http://[^/]+)(/.+?)([^/]+)$!', '$1/cache${2}'.$this->arg('geometry').'/$3', $origin_url);
		return $thumb_url;
	}

	function html_code($append = "", $use_alt_title=true)
	{
		return "<img src=\"{$this->url()}\" ".$this->wxh($use_alt_title)." $append />";
	}
}
