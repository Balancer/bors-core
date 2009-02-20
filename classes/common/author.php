<?
	
class common_author extends base_page_db
{
	function main_db_storage(){ return 'common'; }
	function main_table_storage() { return 'authors'; }

	function field_create_time_storage() { return 'create_time(id)'; }
	function field_modify_time_storage() { return 'modify_time(id)'; }
				
	var $stb_first_name;
	function first_name() { return $this->stb_first_name; }
	function set_first_name($first_name, $db_update = false) { $this->set("first_name", $first_name, $db_update); }
	function field_first_name_storage() { return 'first_name(id)'; }

	var $stb_middle_name;
	function middle_name() { return $this->stb_middle_name; }
	function set_middle_name($middle_name, $db_update = false) { $this->set("middle_name", $middle_name, $db_update); }
	function field_middle_name_storage() { return 'middle_name(id)'; }

	var $stb_last_name;
	function last_name() { return $this->stb_last_name; }
	function set_last_name($last_name, $db_update = false) { $this->set("last_name", $last_name, $db_update); }
	function field_last_name_storage() { return 'last_name(id)'; }

	function field_title_storage() { return ''; }
	function field_description_storage() { return ''; }
	function title() { return $this->last_name()." ".$this->first_name()." ".$this->middle_name(); }
	
	function resources()
	{
		$result = array();

		foreach($this->db()->get_array("SELECT * FROM authors_index WHERE author_id = ".$this->id()) as $x)
			$result[] = class_load($x['class_name'], $x['class_id']);

		return $result;
	}
	
	function find_by_name($last_name, $first_name, $middle_name)
	{
		$db = &new DataBase(common_author::main_db_storage());
		return intval(
			$db->get("SELECT id FROM authors WHERE first_name='".addslashes(trim($first_name)).
				"' AND last_name='".addslashes(trim($last_name)).
				"' AND middle_name='".addslashes(trim($middle_name))."' LIMIT 1"));
	}

	function store_by_name($last_name, $first_name, $middle_name)
	{
		$author_id = common_author::find_by_name($last_name, $first_name, $middle_name);
		if($author_id)
			return $author_id;
		
		$db = &new DataBase(common_author::main_db_storage());
		$db->insert('authors', array(
			'first_name' => trim($first_name),
			'last_name' => trim($last_name),
			'middle_name' => trim($middle_name),
			'int create_time' => time(),
			'int modify_time' => time(),
		));
		
		return intval($db->last_id());
	}
}
