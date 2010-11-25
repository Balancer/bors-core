<?php

/**	Oracle backend (oci), R/O version
*/

class bors_storage_oci extends bors_storage
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

		$object->set_loaded(true);

		print_d($data);

		return true;
	}
}
