<?php

namespace B2;

class Model extends Object
{
	function _item_name() { return @array_pop(explode('_', $this->class_name())); }
	function _item_name_m() { return \blib_grammar::plural($this->_item_name()); }

	function _fields_def()
	{
		return array($this->db_name() => array($this->table_name() => $this->table_fields()));
	}

	function _table_name_def()
	{
		$class_name = $this->class_name();
		// Выбираем последнее слово имени класса в роли имени таблицы по умолчанию.
		if(preg_match('/.+?([a-zA-Z]\w+)$/i', $class_name, $m))
			return \blib_grammar::plural(bors_lower($m[1]));

		return $this->_item_name_m();
	}

	private $storage = NULL;

	// Храним тут, а не в Object, простому объекту бэкенд данных не нужен.
    function storage()
	{
		if($this->storage)
			return $this->storage;

		$storage_class = $this->storage_engine();

		if($storage_class_name = $storage_class)
			return $this->storage = new $storage_class_name($this);

		bors_throw('Undefined storage engine for '.$this);
	}

	// Создаёт данные нового объекта (сам объект по new уже создан)
	function new_instance()
	{
		$tab = $this->table_name();
		if(!$tab)
			bors_throw("Try to get new db instance with empty table_name()");

		if(!$this->get('create_time'))
			$this->set_create_time(time());

		if(!$this->get('modify_time'))
			$this->set_modify_time(time());

		$this->set('owner_id', bors()->user_id());
		$this->set('owner_ip', bors()->client()->ip());
		$this->set('last_editor_id', bors()->user_id());

		$this->storage()->create($this);
		$this->changed_fields = array();

		return $this;
	}

	static function create($data)
	{
		return bors_new(get_called_class(), $data);
	}
}
