<?php

// Используется пока исключительно в РП: rp1990_admin_publications_edit
// При модификациях - учесть

class bors_admin_categories_checklist extends bors_module
{
	function body_data()
	{
		$target = $this->args('target');
		$list_class = $this->args('list_class');
		$map_class  = $this->args('map_class');
		$list     = bors_find_all($list_class, array('by_id' => true, 'order' => 'title'));
		$checked = call_user_func(array($map_class, 'checked'), $target);
		foreach($checked as $x)
			$list[$x->category_id()]->set_attr('checked', true);

		$named_list = array();
		foreach($list as $id => $x)
			$named_list[$id] = $x->title();

		$checked = call_user_func(array($map_class, 'checked'), $this->args('target'));
		$selected = bors_field_array_extract($checked, 'category_id');

		return compact('list_class', 'map_class', 'named_list', 'selected', 'target');
	}

	function on_action_update($data)
	{
		$target = bors_load_uri($data['target']);
		foreach(bors_find_all($data['map_class'], array('publication_id' => $target->id())) as $x)
			$x->delete();

		foreach($data['items'] as $id)
			bors_new($data['map_class'], array(
				'publication_id' => $target->id(),
				'category_id' => $id,
			));

		return go($data['go']);
	}
}
