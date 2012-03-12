<?php

/**
	@title = Управление объектами
*/

class bors_admin_objects_main extends bors_admin_page
{
	function on_action_go_edit($data)
	{
		return go('/_bors/admin/obejcts/'.$data['target_class_name']);
	}
}
