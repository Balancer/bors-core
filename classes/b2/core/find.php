<?php

class b2_core_find extends bors_core_find
{
	// Найти все объекты, соответствующие заданным критериям
	function all($limit1=NULL, $limit2=NULL)
	{
		$args = func_get_args();
		if(count($args) == 0)
			$array = parent::all();
		elseif(count($args) == 1)
			// Формат all($limit)
			$array = parent::all($limit1);
		else
			// Формат all($page, $items_per_page)
			$array = parent::all($limit1, $limit2);

		return blib_array::factory($array);
	}

	function first()
	{
		$res = $this->limit(1)->all();

		if($res->is_value())
		{
			$val = $res->value();
			return $res->pop();
		}

		return new blib_null;
	}
}
