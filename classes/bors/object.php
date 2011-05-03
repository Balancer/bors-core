<?php

// Идёт процесс рефакторинга с переносом функционала base_object в bors_object
class bors_object extends base_object
{
	// Предустановленные автообъекты
	function auto_objects()
	{
		return array(
			'user'  => 'bors_user(user_id)',
			'owner' => 'bors_user(owner_id)',
		);
	}

	// Предустановленные авто целевые объекты
	function auto_targets()
	{
		return array_merge(parent::auto_targets(), array(
			'target' => 'target_class_name(target_object_id)',
		));
	}

	function renderer()
	{
		$renderer_class = $this->get('renderer_class');

		if(!$renderer_class)
			$renderer_class = $this->get('render_engine'); // Старый API, для совместимости.

		if($renderer_class == 'self')
			return $this;

		return $renderer_class ? bors_load($renderer_class, NULL) : NULL;
	}

	function direct_content() { return $this->renderer()->render($this); }
}
