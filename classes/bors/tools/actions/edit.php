<?php

class bors_tools_actions_edit extends bors_object
{
	function pre_parse()
	{
		$object = bors_load_uri($this->id());
		if(!$object)
			bors_throw("Can't load object ".$this->id());

		return go($object->edit_url());
	}
}
