<?php

class bors_pages_db extends bors_page
{
	function can_be_empty() { return false; }
	function _storage_engine_def() { return 'bors_storage_mysql'; }
	function _db_name_def() { return bors_throw(_("This is metaclass. You need to define db_name")); }
	function _table_name_def() { return 'pages'; }

	function _table_fields_def()
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
