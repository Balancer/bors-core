<?php

class bors_admin_module_images extends bors_module
{
	function body_data()
	{
		$target = $this->arg('object', bors()->main_object());
		$types = $this->arg('types', 'bors_image');
		$images = $target->cross_objs($types);

		$sort_order = 0;
		foreach($images as $img)
		{
			if($img->sort_order() > $sort_order)
				$sort_order = $img->sort_order();
		}

		return array_merge(parent::body_data(), array(
			'item' => $target,
			'images' => $images,
			'sort_order' => $sort_order,
			'linkable' => $this->arg('linkable'),
			'image_class' => $this->arg('image_class', 'bors_image'),
			'upload_images_count' => $this->arg('upload_images_count', 4),
			'skip_limits' => $this->arg('skip_limits', false),
			'skip_image_type' => $this->arg('skip_image_type', false),
			'upload_dir' => $this->arg('upload_dir', 'uploads/images'),
			'image_type' => $this->arg('image_type', 0),
			'author_name' => $this->arg('author_name', ''),
			'link_type' => $this->arg('link_type', 'cross'),
		));
	}
}
