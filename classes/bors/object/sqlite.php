<?php

class bors_object_sqlite extends base_object_db
{
	function storage_engine() { return 'bors_storage_sqlite'; }

	// По умолчанию в качестве рабочего файла используется
	// файл в BORS_SITE/data с именем, одноимённым первому слову названия класса
	// Например, /var/www/balancer.ru/bors-site/data/db/balancer.sqlite
	function db_name() { return BORS_SITE.'/data/db/'.preg_replace('!^(\w+)_.*$!', '$1', $this->class_name()).'.sqlite'; }

	// По умолчанию в качестве имени таблицы используется последее
	// слово имени класса во множественном числе.
	function table_name() { return bors_plural(preg_replace('!^.*_(\w+)$!', '$1', $this->class_name())); }

	// Поля объекта в абстрактном классе не определены, так что бросаем исключение,
	// если в готовом проекте мы забудем описать самый главный метод :)
	function table_fields() { bors_throw(ec('Не определены поля таблицы класса ').$this->class_name()); }
}
