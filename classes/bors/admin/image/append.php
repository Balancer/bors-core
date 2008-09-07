<?php

require_once('inc/bors/cross.php');

class bors_admin_image_append extends base_object
{
	function config_class() { return config('admin_config_class');}
	function acl_edit_sections() { return array('*' => 1); }
	function auto_search_index() { return false; }

	function new_instance() { $this->set_id(true); }
	
	function upload_image_file(&$data)
	{
//		print_d($_GET);
//		print_d($data);
	
		$obj = $this->object();
		
		if(!$obj)
			return;
	
		$order = $this->db()->select('bors_cross', 'MAX(`order`)', array('from_class=' => $obj->class_id(), 'from_id=' => $obj->id()));
		
		$last_order = (intval(($order-1)/10)+1)*10;

		foreach($data['tmp_name'] as $n => $tmp_name)
		{
			if(!$tmp_name)
				continue;
		
			$img = object_new_instance('bors_image');
			$img->upload(array(
				'tmp_name' => $tmp_name,
				'name' => $data['name'][$n],
			), $_GET['upload_dir']);
			
			$img->set_title($obj->title(), true);
//			echo "img {$data['name'][$n]}, order={$_GET['sort_order'][$n]}, title={$_GET['image_title'][$n]}<br />";
			$img->set_description(@$_GET['image_title'][$n], true);
			$img->set_author_name(bors()->user()->title(), true);
			$img->set_resolution_limit(@$_GET['image_limit'][$n], true);
			$img->set_image_type(@$_GET['image_type'][$n], true);
			$img->set_original_filename($data['name'][$n], true);
			
			if(empty($_GET['sort_order'][$n]))
				$order = $last_order + 10;
			else
				$order = intval($_GET['sort_order'][$n]);
				
			$last_order = (intval(($order-1)/10)+1)*10;

//			set_loglevel(10);
			bors_add_cross($obj->class_name(), $obj->id(), 'bors_image', $img->id(), $order);
//			set_loglevel(2);
		}
		
		go($obj->admin_url());
		bors_exit(0);
	}
	
	function url() { return '/admin/image/append'; }
	
	function object() { return object_load($_GET['object_to_link']); }
}
