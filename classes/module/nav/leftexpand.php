<?php

class module_nav_leftexpand extends base_page
{
	function local_template_data_set()
	{
		$list = array();

		$root_url = $this->args('root_url', '/');
		$obj = &$this->id();
		$obj_url = $obj->url();

		$root_obj = object_load($root_url);
		
		if($this->args('show_root'))
			$list[] = array('url' => $root_url, 'children' => array(), 'selected' => $root_obj->url() == $obj->url(), 'obj' => $root_obj);

		foreach($root_obj->children() as $child_url)
		{
			$children = array();
			$child = object_load($child_url);
			$selected = $obj_url == $child->url();
			foreach($child->children() as $subchild_url)
			{
				if(!($subchild = object_load($subchild_url)))
					continue;
					
				if($subselected = $obj_url == $subchild->url())
					$selected = true;
				$children[] = array('url' => $subchild->url(), 'children' => false, 'selected' => $subselected, 'obj'=>$subchild);
			}
			if($selected)
				$list[] = array('url' => $child->url(), 'children' => $children, 'selected' => $selected, 'obj' => $child);
			else
				$list[] = array('url' => $child->url(), 'children' => array(), 'selected' => false, 'obj' => $child);
		}

        return array(
			'list' => $list,
		);
    }
}
