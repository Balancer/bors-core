<?php

class bors_abstract_blog extends base_page_paged
{
	function title() { return ec('Блоги'); }
	function nav_name() { return ec('блоги'); }

	function where() { return array('is_public' => 1); }
	function is_reversed() { return true; }
	function is_public_access() { return true; }
}
