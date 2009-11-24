<?php

class bors_admin_main extends base_page
{
	function title() { return ec('Управление системой BORS©'); }
	function nav_name() { return ec('администрирование'); }
	function can_cache() { return false; }
	function admin() { return false; }
}
