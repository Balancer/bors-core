<?php

class bors_links_hidden extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }

	function table_name() { return 'bors_hidden_children'; }
	function table_fields()
	{
		return array(
			'id',
			'child_url',
		);
	}

	static function add($child)
	{
		object_new_instance('bors_links_hidden', array('child_url' => $child->url()));
	}

	static function remove($child)
	{
		$link = bors_find_first('bors_links_hidden', array('child_url' => $child->url()));
		if($link)
			$link->delete();
	}

	static private $_cache = false;
	static function is_hidden($child)
	{
		if(self::$_cache === false)
		{
			self::$_cache = array();
			foreach(bors_find_all('bors_links_hidden', array()) as $x)
				self::$_cache[$x->child_url()] = $x;
		}

		return array_key_exists($child->url(), self::$_cache);
	}
}
