<?php

class bors_file extends base_object_db
{
	function class_title() { return ec('Файл'); }
	function class_title_rp() { return ec('файла'); }
	function class_title_vp() { return ec('файл'); }
	function class_title_m() { return ec('файлы'); }
	function class_title_tpm() { return ec('файлами'); }

	function storage_engine() { return 'bors_storage_mysql'; }
	function table_name() { return 'files'; }
	function table_fields()
	{
		return array(
			'full_path' => array('is_editable' => false),
			'title' => array('title' => ec('Название')),
			'description' => array('title' => ec('Описание'), 'type' => 'bbcode'),
			'relative_path' => array('is_editable' => false),
			'mime_type' => array('is_editable' => false),
			'extension' => array('is_editable' => false),
			'parent_class_name' => array('is_editable' => false),
			'parent_id' => array('is_editable' => false),
			'sort_order' => array('title' => ec('Порядок сортировки')),
			'full_url' => array('is_editable' => false),
			'full_file_name' => array('is_editable' => false),
			'original_filename' => array('is_editable' => false),
			'size' => array('is_editable' => false),
			'owner_id' => array('is_editable' => false),
			'last_editor_id' => array('is_editable' => false),
		);
	}
}
