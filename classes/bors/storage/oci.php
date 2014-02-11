<?php

/**	Oracle backend (oci), R/O version
*/

class bors_storage_oci extends bors_storage implements Iterator
{
	function load($object)
	{
		$select = array();
		$post_functions = array();
		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			$x = $f['name'];
			if($f['name'] != $f['property'])
				$x .= " AS `{$f['property']}`";

			$select[] = $x;

			if(!empty($f['post_function']))
				$post_functions[$f['property']] = $f['post_function'];
		}

		$where = array('`'.$object->id_field().'`=' => $object->id());

		$dummy = array();

		$dbh = new driver_oci($object->db_name());
		$data = $dbh->select($object->table_name(), join(',', $select), $where);

		$object->data = $data;

//		if(!empty($post_functions))
//			self::post_functions_do($object, $post_functions);

		$object->set_is_loaded(true);

//		print_d($data);

		return true;
	}

	function load_array($object, $where)
	{
		$by_id  = popval($where, 'by_id');

		$select = array();
		$post_functions = array();
		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			$x = $f['name'];
			if($f['name'] != $f['property'])
				$x .= " AS `{$f['property']}`";

			$select[] = $x;

			if(!empty($f['post_function']))
				$post_functions[$f['property']] = $f['post_function'];
		}

		$objects = array();
		$class_name = $object->class_name();

		$dbh = new driver_oci($object->db_name());
		foreach($dbh->select_array($object->table_name(), join(',', $select), $where) as $data)
		{
			$object->set_id($data['id']);
			$object->data = $data;
			$object->set_is_loaded(true);

			if($by_id === true)
				$objects[$object->id()] = $object;
			elseif($by_id)
				$objects[$object->$by_id()] = $object;
			else
				$objects[] = $object;

			$object = new $class_name(NULL);
		}

		return $objects;
	}

	private $data;
	private $dbi;
	private $object;
	private $__class_name;

	static function each($class_name, $where)
	{
		$by_id  = popval($where, 'by_id');

		$select = array();
		$post_functions = array();

		$object = new $class_name(NULL);

		foreach(bors_lib_orm::main_fields($object) as $f)
		{
			$x = $f['name'];
			if($f['name'] != $f['property'])
				$x .= " AS `{$f['property']}`";

			$select[] = $x;

			if(!empty($f['post_function']))
				$post_functions[$f['property']] = $f['post_function'];
		}

		$class_name = $object->class_name();

		$iterator = new bors_storage_oci();
		$iterator->__class_name = $class_name;

		$iterator->dbi = driver_oci::factory($object->db_name())->each($object->table_name(), join(',', $select), $where);
		return $iterator;
	}

	// void Iterator::rewind ( void )
	// Rewinds back to the first element of the Iterator.
	// Any returned value is ignored.
	// Вызывается первый раз.
    public function rewind()
    {
		$this->dbi->rewind();
    }

	// void Iterator::next ( void )
	// Moves the current position to the next element.
	// Any returned value is ignored.
	// Вызывается после выдачи первого элемента, перед выдачей следующих
    public function next()
    {
		$this->dbi->next();
    }

	// boolean Iterator::valid ( void )
	// This method is called after Iterator::rewind() and Iterator::next() to check if the current position is valid.
	// The return value will be casted to boolean and then evaluated. Returns TRUE on success or FALSE on failure.
    public function valid()
    {
		return $this->dbi->valid();
    }

	// mixed Iterator::current ( void )
	// Returns the current element.
	// Can return any type.
    public function current()
    {
    	$data = $this->dbi->current();
		$class_name = $this->__class_name;
		$object = new $class_name($data['id']);
//		$object->set_id($data['id']);
		$object->data = $data;
		$object->set_is_loaded(true);
		return $object;
    }

	// scalar Iterator::key ( void )
	// Returns the key of the current element.
	// Returns scalar on success, or NULL on failure.
	// Not implemented
    public function key()
    {
		return NULL;
    }
}
