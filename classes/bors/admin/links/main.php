<?php

class bors_admin_links_main extends bors_admin_page
{
	function title() { return ec('Управление связями'); }
	function nav_name() { return ec('связи'); }

	function config_class() { return 'bors_admin_links_config'; }

	function pre_show()
	{
		config_set('nav_name_lower', false);
		return parent::pre_show();
	}

	function body_data()
	{
		$items = bors_find_all('bors_link', $this->where(array(
			'order' => '-create_time',
			'page' => $this->page(),
			'per_page' => $this->items_per_page(),
		)));

		return array_merge(parent::body_data(), compact('items'));
	}

	function where($cond)
	{
		return array_merge(array(
				'(from_class < to_class OR (from_class = to_class AND from_id < to_id))',
		), $cond);
	}

	function total_items() { return objects_count('bors_link', $this->where(array())); }
	function items_around_page() { return 20; }
}
