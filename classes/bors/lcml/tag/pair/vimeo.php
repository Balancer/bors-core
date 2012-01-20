<?php

/**
	Тэг, отображающий видео с хостинга vimeo.com
	Пример использования: [vimeo]...[/vimeo]
*/

class bors_lcml_tag_pair_vimeo extends bors_lcml_tag_pair
{
	function html($id, &$params)
	{
		$width  = defval_ne($params, 'width',  600);
		$height = defval_ne($params, 'height', 450);

		if(($self = defval($params, 'self')) && ($self->class_name() == 'balancer_board_post' || $self->class_name() == 'forum_post'))
		{
			if(!bors_find_first('balancer_board_posts_object', array(
					'post_id' => $self->id(),
//					'target_class_id' => class_name_to_id('bors_external_vimeo'),
					'target_class_name' => 'bors_external_vimeo',
					'target_object_id' => $id,
			)))
				object_new_instance('balancer_board_posts_object', array(
					'post_id' => $self->id(),
					'target_class_id' => class_name_to_id('bors_external_vimeo'),
					'target_class_name' => 'bors_external_vimeo',
					'target_object_id' => $id,
					'target_create_time' => $self->create_time(),
					'target_score' => $self->score(),
			));
		}

		return "<iframe src=\"http://player.vimeo.com/video/{$id}\" width=\"{$width}\" height=\"{$height}\" frameborder=\"0\"></iframe>\n";
	}
}
