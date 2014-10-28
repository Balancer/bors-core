<?php

class bors_admin_module_items extends bors_module
{
	function body_data()
	{
		$items = $this->args('items');
		if(is_array($items))
			$foo = $items[0];
		else
			$foo = $items->first();

		$view = $this->arg('view', bors()->main_object());

		$fields = $view->get('item_fields');

		if(!$fields)
			$fields = $foo->item_list_admin_fields();

		if(!$fields)
			$fields = array(
				'mtime' => ec('Дата изменения'),
				'title' => ec('Название'),
				'id' => ec('ID'),
			);

		$parsed_fields = array();
		$sortable = array();
		foreach($fields as $p => $t)
		{
			if(is_numeric($p))
			{
				$p = $t;
				$x = bors_lib_orm::parse_property($view->main_class(), $p);
				$t = defval($x, 'title', $p);
				if(!empty($x['admin_sortable']))
					$sortable[] = $p;
			}

			$parsed_fields[$p] = $t;
		}

		$view->set_attr('_sortable_append', $sortable);

		$data = array();

		return array_merge(parent::body_data(), $data, array(
			'item_fields' => $parsed_fields,
			'items' => $items,
			'view' => $view,
		));
	}
}
