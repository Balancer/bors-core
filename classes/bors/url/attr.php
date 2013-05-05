<?php

class bors_url_attr extends url_base
{
	function url($page=NULL) { return $this->object()->attr('url'); }
}
