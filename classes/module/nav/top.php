<?php

class module_nav_top extends base_page
{
	private $visited_pairs;

	function data_providers()
	{
		$this->visited_pairs = array();

		$obj = &$this->id();

        return array('links' => $this->link_line($obj));
    }

    function link_line($obj)
    {
		$links = array();
	
		if(!$obj)
			return $links;
	
		$url = $obj->url();
		
		foreach($obj->parents() as $parent_url)
        {
			$parent = object_load($parent_url);

			if(!$parent || $parent_url == $url)
				continue;

            if(!$this->visited($parent_url, $url))
            {
				$parents_lines = $this->link_line($parent);
				$added = false;
				foreach($parents_lines as $p_line)
				{
					if(!$p_line)
						continue;

					$p_line[] = $obj;
					$links[] = $p_line;
					$added = true;
				}

				if(!$added)
					$links[] = array($obj);
            }
        }

		if(!$links)
			$links[] = array($obj);

        return $links;
    }
	
	function visited($parent, $child)
	{
		if(empty($this->visited_pairs["{$parent}|#|{$child}"]))
		{
			$this->visited_pairs["{$parent}|#|{$child}"] = true;
			return false;
		}
		
		return true;
	}
}
