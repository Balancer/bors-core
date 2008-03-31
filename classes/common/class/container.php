<?
	
class common_class_container extends base_page_db
{
	function main_db_storage(){ return 'common'; }
				
	var $stb_contains_class_name;
	function contains_class_name() { return $this->stb_contains_class_name; }
	function set_contains_class_name($contains_class_name, $db_update = false) { $this->set("contains_class_name", $contains_class_name, $db_update); }
	function field_contains_class_name_storage() { return 'class_name(id)'; }

	var $stb_contains_class_id;
	function contains_class_id() { return $this->stb_contains_class_id; }
	function set_contains_class_id($contains_class_id, $db_update = false) { $this->set("contains_class_id", $contains_class_id, $db_update); }
	function field_contains_class_id_storage() { return 'class_id(id)'; }

	function contains_class()
	{
		return class_load($this->contains_class_name(), $this->contains_class_id());
	}
}
