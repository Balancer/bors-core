<?php

// config_set('default_template', 'default/simple.html');

class bors_ext_admin_main extends base_page
{
	//TODO: добавить access
	function title() { return ec("Администрирование"); }

	function body_engine() { return 'body_lcmlbbh'; }
}
