<?php

class blib_objects
{
	static function sort_order_renum($class_name, $start = 10, $step = 10, $where = array())
	{
		$idx = $start - $step;
		setdef($where, 'order', 'sort_order');
		$sort_property_name = defval($where, 'order');
		foreach(bors_each($class_name, $where) as $x)
			$x->set($sort_property_name, $idx+=$step);
	}

	static function sort_order_up($object, $where = array())
	{
		setdef($where, 'order', 'sort_order');
		$sort_property_name = defval($where, 'order');
		$prev = NULL;
		foreach(bors_each($object->class_name(), $where) as $x)
		{
			if($x->id() != $object->id())
			{
				$prev = $x;
				continue;
			}

			// В обработке дошли до нашего объекта. Меняем местами порядок сортировки с предыдущим:
			if(!$prev) // Если наш объект первый в списке и до него не было, то он итак первый,
				break; // Не делаем больше ничего

			$prev_order = $prev->get($sort_property_name);
			$object_order = $object->get($sort_property_name);
			$prev->set($sort_property_name, $object_order);
			$object->set($sort_property_name, $prev_order);
			// Принудительно сохраним изменения
			$prev->save();
			$object->save();
			// После того, как поменяли местами, дальше пахать не нужно.
			break;
		}

		// Перенумеруем для порядка.
		self::sort_order_renum($object->class_name(), 10, 10, $where);
	}

	// Все комментарии по работе как в предыдущем методе
	static function sort_order_down($object, $where = array())
	{
		setdef($where, 'order', 'sort_order');
		$sort_property_name = defval($where, 'order');
		$prev = NULL;
		// Разница только в том, что крутим мы перевёрнутый массив, от конца к началу
		foreach(array_reverse(bors_each($object->class_name(), $where)) as $x)
		{
			if($x->id() != $object->id())
			{
				$prev = $x;
				continue;
			}

			if(!$prev)
				break;

			$prev_order = $prev->get($sort_property_name);
			$object_order = $object->get($sort_property_name);
			$prev->set($sort_property_name, $object_order);
			$object->set($sort_property_name, $prev_order);
			$prev->save();
			$object->save();
			break;
		}

		self::sort_order_renum($object->class_name(), 10, 10, $where);
	}
}
