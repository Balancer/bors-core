<?php

class bors_pages_module_paginated_items extends bors_module
{
	function body_data()
	{
		$items = $this->args('items');
		$data = $this->args('table_columns');

		if($view = $this->args('view'))
			$table_classes = [$view->layout()->table_class()];
		else
			$table_classes = [ 'btab', 'w100p', 'table', 'table-bordered', 'table-striped', 'table-hover', 'table-heading', 'table-datatable'];

		if($ajax_sortable = $this->args('ajax_sortable'))
		{
			jquery_tablesorter::on("'.tablesorter'");
			$table_classes[] = 'tablesorter';
		}

		if(!$data)
		{
			if($class_name = $this->args('class'))
				$foo = new $class_name(NULL);
			else
			{
				if(is_array($items))
					$foo = reset($items);
				else
					$foo = $items->first();
			}

			if($foo)
			{
				if($this->args('is_admin_list'))
					$data = $foo->get('item_list_admin_fields');

				if(!$data)
					$data = $foo->get('item_list_fields');

				if(!$data)
				{
					if($this->args('is_admin_list'))
						$data = array(
							'admin()->imaged_titled_link()' => ec('Название'),
							'id' => ec('ID'),
						);
					else
						$data = array(
							'title' => ec('Название'),
							'id' => ec('ID'),
						);
				}
			}
		}

		$body_data = array(
			'item_list_fields' => $data,
			'items' => $items,
		) + parent::body_data();

		$more = false;
		if(($limit = $this->args('more_limit')) && count($items) >= $limit)
		{
			$item = $items[0];
			if($this->args('more') == 'search')
				$more = dirname($item->url()).'/search/?q='.bors()->request()->data('q');
		}

		$body_data['more'] = $more;
		$body_data['table_classes'] = join(' ', $table_classes);

		return $body_data;
	}

	function make_sortable_th($property, $title)
	{
		return bors_pages_helper::make_sortable_th($this, $property, $title);
	}
}
