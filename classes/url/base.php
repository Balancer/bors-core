<?php

class url_base extends bors_object_simple
{
	function url() { return $this->url_ex(NULL); }
	function url_ex($args) { return '/'; }

	function object() { return $this->id(); }
}
