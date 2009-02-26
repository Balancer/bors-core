<?php

class module_nav_leftexpand extends base_page
{
	function local_data()
	{
		$list = array();

		$root_url = $this->args('root_url', '/');
		if(!$obj = &$this->id())
			return $list;
		
		$obj_url = $obj->url();

		$root_obj = object_load($root_url);
		
		if($this->args('show_root'))
			$list[] = array('url' => $root_url, 'children' => array(), 'selected' => $root_obj->url() == $obj->url(), 'obj' => $root_obj);

//		print_d($root_obj->children());
		foreach($root_obj->children() as $child_url)
		{
			$children = array();
			$child = object_load($child_url);
			if(!$child)
			{
				echo "Unknown child {$child_url}<br />";
				continue;
			}
			
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
