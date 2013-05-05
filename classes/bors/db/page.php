<?php

class bors_db_page extends bors_object_db
{
	function table_fields()
	{
		return array(
			'id',
			'url',
			'title',
			'source',
			'access_level',
			'description',
			'description_source',

			'create_time',
			'modify_time',
		);
	}

	static function id_prepare($id)
	{
		if(is_numeric($id))
			return $id;

		$path = blib_urls::path($id);

		$page = bors_find(get_called_class())
			->eq('url', $path)
			->first();

		if($page)
			return $page;

		return NULL;
	}

	function url($page=NULL) { return $this->data['url']; }
}
