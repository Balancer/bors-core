<?php

class bors_ajax_script extends bors_page
{
	var $body_template_ext = 'tpl.js';

	function content() { return $this->body(); }

	function pre_show()
	{
		$x = parent::pre_show();
		header('Content-type: application/x-javascript; charset: utf-8');
		return $x;
	}
}
