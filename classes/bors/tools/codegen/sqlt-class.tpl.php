<?php

class %class_name% extends base_object_db
{
	function title() { return ec('%class_title%'); }

	function nav_name() { return ec('%class_title_lower%'); }
	function table_name() { return '%table_name%'; }
	function table_fields()
	{
		return array(
			%class_field_names%
		);
	}
{$auto_objects_code}
{$ftargets}
	function url() { return config('main_host_url').'/%admin_path%/'.$this->id().'/'; }
}
