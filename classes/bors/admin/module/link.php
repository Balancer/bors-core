<?php

class bors_admin_module_link extends bors_module
{
	function body_data()
	{
		$target = $this->arg('object', bors()->main_object());
		$types = $this->arg('types', '-bors_image,-bors_file');
		return array_merge(parent::body_data(), array(
			'target' => $target,
			'linked' => $target->cross_objs($types),
			'linkable' => $this->arg('linkable'),
		));
	}
}
