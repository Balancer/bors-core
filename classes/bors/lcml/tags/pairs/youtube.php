<?php

class bors_lcml_tags_pairs_youtube extends bors_lcml_tags_pair
{
	static function html($id, &$params)
	{
		$width  = @$params['width']  ? $params['width']  : '640';
		$height = @$params['height'] ? $params['height'] : '390';

		if(($self = defval($params, 'self')) && ($self->class_name() == 'balancer_board_post' || $self->class_name() == 'forum_post'))
		{
			if(!bors_find_first('balancer_board_posts_object', array(
					'post_id' => $self->id(),
					'target_class_name' => 'bors_external_youtube',
					'target_object_id' => $id,
			)))
				bors_new('balancer_board_posts_object', array(
					'post_id' => $self->id(),
					'target_class_id' => class_name_to_id('bors_external_youtube'),
					'target_class_name' => 'bors_external_youtube',
					'target_object_id' => $id,
					'target_create_time' => $self->create_time(),
					'target_score' => $self->score(),
			));
		}

		return "<iframe width=\"{$width}\" height=\"{$height}\" src=\"http://www.youtube.com/embed/{$id}\" frameborder=\"0\" allowfullscreen></iframe>\n";
	}

	static function text($id, &$params)
	{
		if(($self = defval($params, 'self')) && ($self->class_name() == 'balancer_board_post' || $self->class_name() == 'forum_post'))
		{
			if(!bors_find_first('balancer_board_posts_object', array(
					'post_id' => $self->id(),
					'target_class_name' => 'bors_external_youtube',
					'target_object_id' => $id,
			)))
				bors_new('balancer_board_posts_object', array(
					'post_id' => $self->id(),
					'target_class_id' => class_name_to_id('bors_external_youtube'),
					'target_class_name' => 'bors_external_youtube',
					'target_object_id' => $id,
					'target_create_time' => $self->create_time(),
					'target_score' => $self->score(),
			));
		}

		return ec("Видео на YouTube: $id\n");
	}
}
