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

	static function total_items($self) { return @$self->__total_items; }
	static function set_total_items($self, $count) { return $self->__total_items = $count; }
	static function items_offset() { $p = $self->page(); return $p > 1 ? ($self->page()-1)*$self->items_per_page() : 0; }

	static function pages_links_nul($self, $css='pages_select', $text = NULL, $delim = '', $show_current = true, $use_items_numeration = false, $around_page = NULL)
	{
		if(is_array($css))
			extract($css);
		else
		{
			$container_css = $css;
		}

		if($self->total_pages() < 2)
			return '';

		if($text === NULL)
			$text = ec('Страницы:');

		include_once('inc/design/page_split.php');

		if(!$around_page)
			$around_page = $self->items_around_page();

		$pages = pages_show($self, $self->total_pages(), $around_page,
			$show_current, 'current_page', 'select_page',
			$use_items_numeration, $self->items_per_page(), $self->total_items()
		);

		if($self->is_reversed())
			$pages = array_reverse($pages);

		return '<div class="'.$container_css.'">'.$text.join($delim, $pages).'</div>';
	}

	static function __unit_test_helper($self, $data) { return $self->test_attr().'/'.$data.'=ok'; }

	static function __unit_test($suite)
	{
		$page = bors_load('bors_page', NULL);
		$page->set_attr('test_attr', 'attr');
		$suite->assertEquals('attr/data=ok', $page->__unit_test_helper('data'));
	}
}
