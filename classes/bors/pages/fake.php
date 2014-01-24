<?php

class bors_pages_fake extends bors_page
{
	function _title_def() { return defval($this->id(), 'title'); }
	function _body_def() { return defval($this->id(), 'body'); }
}
