<?php

class bors_di_page
{
	static function template_metas($self, $indent = '')
	{
		$result = array();

		$data = bors_template::page_data($self);

		if(!empty($data['meta']))
			foreach($data['meta'] as $name => $content)
				$result[] = $indent . \HtmlObject\Element::meta()->name($name)->content($content);

		return join("\n", $result);
	}

	static function __unit_test_helper($self, $data) { return $self->test_attr().'/'.$data.'=ok'; }

	static function __unit_test($suite)
	{
		$page = bors_load('bors_page', NULL);
		$page->set_attr('test_attr', 'attr');
		$suite->assertEquals('attr/data=ok', $page->__unit_test_helper('data'));
	}
}
