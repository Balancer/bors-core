<?php

require_once('inc/bors/cross.php');

class bors_admin_files_append extends base_object
{
	var $_last_file;

	function config_class() { return config('admin_config_class');}
	function acl_edit_sections() { return array('*' => 1); }
	function auto_search_index() { return false; }

	function new_instance() { $this->set_id(true); }
	function storage() { return $this; }
	function skip_save() { return true; }

	function upload_file(&$data, &$get)
	{
//		var_dump($data); var_dump($get); exit();

		$obj = $this->object();

		// Отладка массивов на http://mbfi.wrk.ru/admin/services/14
		foreach($data['tmp_name'] as $idx => $tmp_file)
		{
			if(empty($tmp_file))
				continue;

			$sort_order = intval($get['sort_orders'][$idx]);
			$file_class = defval($get, 'file_class', 'bors_file');

			$file = $file_class::upload(array(
				'tmp_name' => $tmp_file,
				'name' => $data['name'][$idx],
				'type' => $data['type'][$idx],
				'error' => $data['error'][$idx],
				'size' => $data['size'][$idx],
				'upload_dir' => $get['upload_dir'],
			));

			$this->_last_file = $file;

			if(!($title = @$get['file_titles'][$idx]))
				$title = preg_replace('!^(.+)\.([^\.]+)$!', '$1', $data['name'][$idx]);

			$file->set_title($title);
			$file->set_description(@$get['file_descriptions'][$idx]);
//			echo $file->title(), $file->description(); exit();

			if($obj)
			{
				$file->set_parent_class_id($obj->class_id(), true);
				$file->set_parent_object_id($obj->id(), true);
			}

			$file->set_sort_order($sort_order, true);

			if($obj)
			{
				switch(@$get['link_type'])
				{
					case 'cross':
						bors_link::link($obj->extends_class_name(), $obj->id(), $file_class, $file->id(), array('sort_order' => $sort_order));
						break;
					case 'parent':
						break;
					default:
						bors_throw('Append files with unknown link type');
						break;
				}
			}
		}
	}

	function object() { return empty($_GET['object_to_link']) ? NULL : object_load($_GET['object_to_link']); }
	function pre_show() { return go_ref($this->object()->admin_url()); }

	function admin_url() { return $this->object() ? $this->object()->admin_url() : $this->_last_file->admin_url(); }
}
