<?php

class bors_tests_templates_smarty2 extends bors_page
{
	function page_template_class() { return 'bors_templates_smarty'; }

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			'var1' => ec('значение переменной 1'),
			'var3' => array(1,2,3),
		));
	}

	function page_data()
	{
		return array_merge(parent::page_data(), array(
			'glob_var2' => ec('значение глобальной переменной 2'),
		));
	}
}
