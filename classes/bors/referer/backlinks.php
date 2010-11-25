<?php

class bors_referer_backlinks extends base_page
{
	function title() { return ec('Внешние ссылки на ').$this->object()->class_name_vp().' '.$this->objecet()->title(); }
	function object() { return $this->__havec('object') ? $this->__lastc() : $this->__setc(object_load($this->id())); }
	function local_data()
	{
		return array(
			'searches' => objects_array('bors_referer_search', array(
				'target_class_name' => $this->object()->class_name(), 
				'target_object_id' => $this->object()->id(),
				'order' => '-count',
			)),
			'links' => objects_array('bors_referer_links', array(
				'target_class_name' => $this->object()->class_name(), 
				'target_object_id' => $this->object()->id(),
				'order' => '-count',
			)),
		);
	}

	function loaded() { return !!$this->object(); }

	function pre_show()
	{
		if(bors()->client()->is_bot())
			return go('/');

		return false;
	}
}
