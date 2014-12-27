<?php

// Используется пока для sitemap: bors_system_sitemap_index

class bors_xml extends bors_pages_pure
{
	function body_template_ext() { return 'xml'; }
	function use_temporary_static_file() { return false; }

	function pre_show()
	{
		header('Content-Type: application/xml; charset='.$this->output_charset());
		config_set('debug.timing', false); // Чтобы не мусорить комментарием в конце XML
		return false;
	}

	function body_data()
	{
		return array_merge(parent::body_data(), array(
			'smarty_auto_literal' => true,
		));
	}
}
