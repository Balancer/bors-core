<?php

function lp_rutube($id, &$params)
{
	$width  = @$params['width']  ? $params['width']  : '470';
	$height = @$params['height'] ? $params['height'] : '353';

	if(($self = defval($params, 'self')) && ($self->class_name() == 'balancer_board_post' || $self->class_name() == 'forum_post'))
	{
		object_new_instance('balancer_board_posts_object', array(
			'post_id' => $self->id(),
			'target_class_id' => class_name_to_id('bors_external_rutube'),
			'target_class_name' => 'bors_external_rutube',
			'target_object_id' => $id,
			'target_create_time' => $self->create_time(),
			'target_score' => $self->score(),
		));
	}

	return "<OBJECT width=\"$width\" height=\"$height\">"
		."<PARAM name=\"movie\" value=\"http://video.rutube.ru/$id\"></PARAM>"
		."<PARAM name=\"wmode\" value=\"window\"></PARAM>"
		."<PARAM name=\"allowFullScreen\" value=\"true\"></PARAM>"
		."<EMBED src=\"http://video.rutube.ru/$id\" type=\"application/x-shockwave-flash\" wmode=\"window\" width=\"$width\" height=\"$height\" allowFullScreen=\"true\" ></EMBED>"
		."</OBJECT>";
}
