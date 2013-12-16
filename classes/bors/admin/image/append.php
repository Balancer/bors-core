<?php

require_once('inc/bors/cross.php');

class bors_admin_image_append extends bors_object
{
	var $_last_image;

	// Перед сносом проверить на http://admin.aviaport.ru/directory/persons/50/ от простого пользователя
	function config_class() { return config('admin_config_class');}
	function acl_edit_sections() { return array('*' => 1); }
	function auto_search_index() { return false; }

	function new_instance() { $this->set_id(true); }
	function skip_save() { return true; }

	function access() { return $this->object()->access(); }

	function upload_image_file(&$data, &$get)
	{
		$obj = $this->object();

		if(!$obj)
			return;
//var_dump($data); var_dump($get); echo $obj; exit();
		foreach($data['tmp_name'] as $idx => $tmp_file)
		{
			if(empty($tmp_file))
				continue;

			$idata = getimagesize($tmp_file);
//			var_dump($idata);

			$maxw = config('image_upload_max_width', 2048);
			$maxh = config('image_upload_max_height', 2048);
			if(!$idata || !$idata[0] || $idata[0] > $maxw || $idata[1] > $maxh)
			{
				if(!is_array($idata) || empty($idata[0]) || empty($idata[1]))
					$err = ec('не могу определить размеры изображения: ').print_r($idata, true);
				else
					$err = ec("размер изображения {$idata[0]}x{$idata[1]} при лимите {$maxw}x{$maxh}");

				add_session_message(ec('Ошибка загрузки изображения: ').$err, array('type' => 'error'));
				debug_hidden_log('image-upload-error', "Image upload error: if(!".print_r($idata, true)." || !{$idata[0]} || {$idata[0]} > ".config('image_upload_max_width', 2048).	" || {$idata[1]} > ".config('image_upload_max_height', 2048).')');
				return;
			}

			$sort_order = intval($get['sort_order'][$idx]);
			$image_class = defval($get, 'image_class', 'bors_image');

			$img = object_new($image_class);

			if(!$sort_order)
			{
				$cross_order = $img->db()->select('bors_cross', 'MAX(`sort_order`)', array('from_class=' => $obj->class_id(), 'from_id=' => $obj->id()));
				$parent_order = $img->db()->select('bors_images', 'MAX(`sort_order`)', array('parent_class_id=' => $obj->class_id(), 'parent_object_id=' => $obj->id()));

				$sort_order = max($cross_order, $parent_order);
				$sort_order = (intval(($sort_order-1)/10)+1)*10;
			}

			$img->new_instance();
			$img->upload(array(
				'tmp_name' => $tmp_file,
				'name' => $data['name'][$idx],
			), $get['upload_dir']);

			$img->set_title(@$get['image_title'][$idx], true);
			$img->set_description(@$get['image_description'][$idx], true);
			$img->set_author_name(defval($get, 'author_name', object_property(bors()->user(), 'title')), true);
			$img->set_resolution_limit(@$get['image_limit'][$idx], true);
			$img->set_image_type(@$get['image_type'][$idx], true);
			$img->set_original_filename($data['name'][$idx], true);
			$img->store();

			$this->_last_image = $img;

			switch(@$get['link_type'])
			{
				case 'cross':
					bors_link::link($obj->extends_class_name(), $obj->id(), $image_class, $img->id(), array('sort_order' => $sort_order));
					break;
				case 'parent':
					$img->set_parent_class_id($obj->class_id(), true);
					$img->set_parent_object_id($obj->id(), true);
					$img->set_sort_order($sort_order, true);
					break;
				default:
					bors_exit('Append image with unknown link type');
					break;
			}
		}
	}

	function object() { return empty($_GET['object_to_link']) ? NULL : object_load($_GET['object_to_link']); }
	function pre_show() { return go_ref($this->object()->admin_url()); }

	function admin_url() { return $this->object() ? $this->object()->admin_url() : $this->_last_image->admin_url(); }
}
