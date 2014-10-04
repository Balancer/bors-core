<?php

class bors_referer_backlinks extends bors_page
{
	function title() { return ec('Внешние ссылки на ').$this->object()->class_name_vp().' '.$this->objecet()->title(); }
	function object() { return $this->__havec('object') ? $this->__lastc() : $this->__setc(object_load($this->id())); }
	function body_data()
	{
		return array(
			'searches' => bors_find_all('bors_referer_search', array(
				'target_class_name' => $this->object()->class_name(), 
				'target_object_id' => $this->object()->id(),
				'order' => '-count',
			)),
			'links' => bors_find_all('bors_referer_links', array(
				'target_class_name' => $this->object()->class_name(), 
				'target_object_id' => $this->object()->id(),
				'order' => '-count',
			)),
		);
	}

	function is_loaded() { return (bool) $this->object(); }

	function pre_show()
	{
		if(bors()->client()->is_bot())
			return go('/');

		return false;
	}
}
