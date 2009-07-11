<?php

class dataprovider_dblist extends base_object
{
	function storage_engine() { return NULL; }

	function fill()
	{
		$obj = $this->id();
	
		$dbh = &new DataBase($obj->main_db());
	
		$list = array();

		$where = "";
		if($w = $obj->where())
		{
			require_once('inc/mysql.php');
			$where = mysql_where_compile($w);
		}

		$join = "";
		if($jj = $obj->left_join())
			foreach($jj as $j)
				$join .= "LEFT JOIN {$j} ";

		if($jj = $obj->inner_join())
			foreach($jj as $j)
				$join .= "INNER JOIN {$j} ";

//		echo "obj=".get_class($obj).", item_class=".$obj->item_class().", list_name=".$obj->list_name()."<br/>";
		
		$item_class_name = $obj->item_class();

		if($obj->limit())
		{
			if($obj->page() > 1)
				$limit = 'LIMIT '.(($obj->page()-1)*$obj->limit()).','.$obj->limit();
			else
				$limit = 'LIMIT '.$obj->limit();
		}
		else
			$limit = '';

		$obj->set('total_items', $dbh->get("SELECT COUNT(*) FROM {$obj->main_table()} {$join} {$where}"), false);
		$obj->set('items_per_page', $obj->limit(), false);

		$query = "SELECT DISTINCT `".addslashes($obj->main_table())."`.`".addslashes($obj->id_field())."` FROM `".addslashes($obj->main_table())."` $join $where ORDER BY {$obj->order()} {$limit}";
		
//		echo $query;
		
		foreach($dbh->get_array($query, false) as $id)
		{
			if($item_class_name)
			{
				if($x = object_load($item_class_name, $id))
					$list[] = $x;
			}
			else
				$list[] = $id;
		}

//		print_d($list);

		$this->add_template_data($obj->list_name(), $list);
	}
	
/*	function where() { return NULL; }
	function left_join()   { return array(); }
	function inner_join()  { return array(); }
	function id_field()	   { return 'id'; }
	function list_name()	{ return 'list'; }*/
}
