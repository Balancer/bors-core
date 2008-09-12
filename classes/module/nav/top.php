<?php

class module_nav_top extends base_page
{
	private $visited_pairs;

	function local_template_data_set()
	{
		$this->visited_pairs = array();

		$obj = &$this->id();

        return array(
			'links' => $this->link_line($this->args('show_self', true)),
			'nav_obj' => $obj,
			'delim' => $this->args('delim', ' &#187; '),
		);
    }

    function link_line($show_self = true, &$shown = array())
    {
		$obj = $this->id();

		$result = array(array());
	
		if(!$obj)
			return $result;
			
		if(@$shown["$obj"])
			return $result;
	
		$show["$obj"] = true;

		if(!$obj->parents())
			return $result;

		$result = array();
		foreach($obj->parents() as $parent)
		{
			$links = array();
		
			if($parent == 'http:///')
			{
				debug_hidden_log('internal-errors', "Incorrect parent url for '{$obj}': $parent");
				continue;
			}
				
			$parent_obj = object_load($parent);
			if(!$parent_obj || $parent_obj == $obj)
				continue;
			
			$shown[] = $parent_obj;

			$parent_nav = object_load($this->class_name(), $parent_obj);
			$parent_link_line = $parent_nav->link_line(false, $shown);
				
			for($i = 0; $i < count($parent_link_line); $i++)
				$parent_link_line[$i][] = $parent_obj;

			$result = array_merge($result, $parent_link_line);
		}
		
		if(empty($result))
			$result = array(array());
		
		if($show_self)
			for($i = 0; $i < count($result); $i++)
				$result[$i][] = $obj;
		
		return $result;
	}
}
