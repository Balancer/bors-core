<?php

class bors_url_attr extends url_base
{
	function url() { return $this->object()->attr('url'); }
}
