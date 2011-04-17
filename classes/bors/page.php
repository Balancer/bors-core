<?php

class bors_page extends base_page
{
	function page_template_class() { return config('page_template_class'); }
	// Можно не указывать, если оно равно page_template_class
	function body_template_class() { return config('body_template_class', $this->page_template_class()); }
}
