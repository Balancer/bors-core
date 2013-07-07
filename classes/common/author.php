<?php

class common_author extends bors_page_db
{
	function db_name(){ return 'common'; }
	function table_name() { return 'authors'; }

	function table_fields()
	{
		return array(
			'id',
			'first_name',
			'middle_name',
			'last_name',
			'create_time',
			'modify_time',
		);
	}

	function title() { return join(' ', array($this->last_name(), $this->first_name(), $this->middle_name())); }

	function resources()
	{
		$result = array();

		foreach($this->db()->get_array("SELECT * FROM bors_authors_index WHERE author_id = ".$this->id()) as $x)
			$result[] = class_load($x['target_class_name'], $x['target_object_id']);

		return $result;
	}

	function find_by_name($last_name, $first_name, $middle_name)
	{
		$db = new DataBase(common_author::db_name());
		return intval(
			$db->get("SELECT id FROM bors_authors WHERE first_name='".addslashes(trim($first_name)).
				"' AND last_name='".addslashes(trim($last_name)).
				"' AND middle_name='".addslashes(trim($middle_name))."' LIMIT 1"));
	}

	function store_by_name($last_name, $first_name, $middle_name)
	{
		$author_id = common_author::find_by_name($last_name, $first_name, $middle_name);
		if($author_id)
			return $author_id;

		$db = new DataBase(common_author::db_name());
		$db->insert('bors_authors', array(
			'first_name' => trim($first_name),
			'last_name' => trim($last_name),
			'middle_name' => trim($middle_name),
			'int create_time' => time(),
			'int modify_time' => time(),
		));

		return intval($db->last_id());
	}
}
