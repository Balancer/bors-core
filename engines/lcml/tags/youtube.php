<?php

function lp_youtube($id, &$params)
{
	$width  = @$params['width']  ? $params['width']  : '425';
	$height = @$params['height'] ? $params['height'] : '344';

	if(($self = defval($params, 'self')) && ($self->class_name() == 'balancer_board_post' || $self->class_name() == 'forum_post'))
	{
		object_new_instance('balancer_board_posts_object', array(
			'post_id' => $self->id(),
			'target_class_id' => class_name_to_id('bors_external_youtube'),
			'target_class_name' => 'bors_external_youtube',
			'target_object_id' => $id,
			'target_create_time' => $self->create_time(),
			'target_score' => $self->score(),
		));
	}

	return "<object width=\"{$width}\" height=\"{$height}\"><param name=\"movie\" value=\"http://www.youtube.com/v/{$id}&hl=ru&fs=1&\"></param><param name=\"allowFullScreen\" value=\"true\"></param><param name=\"allowscriptaccess\" value=\"always\"></param><embed src=\"http://www.youtube.com/v/{$id}&hl=ru&fs=1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" width=\"{$width}\" height=\"{$height}\"></embed></object>\n";
}
