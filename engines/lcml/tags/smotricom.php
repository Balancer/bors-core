<?php

function lp_smotricom($id, &$params)
{
	$width  = @$params['width']  ? $params['width']  : '425';
	$height = @$params['height'] ? $params['height'] : '344';

	if(($self = defval($params, 'self')) && ($self->class_name() == 'balancer_board_post' || $self->class_name() == 'forum_post'))
	{
		if(!bors_find_first('balancer_board_posts_object', array(
				'post_id' => $self->id(),
//				'target_class_id' => class_name_to_id('bors_external_youtube'),
				'target_class_name' => 'bors_external_smotricom',
				'target_object_id' => $id,
		)))
			object_new_instance('balancer_board_posts_object', array(
				'post_id' => $self->id(),
				'target_class_id' => class_name_to_id('bors_external_smotricom'),
				'target_class_name' => 'bors_external_smotricom',
				'target_object_id' => $id,
				'target_create_time' => $self->create_time(),
				'target_score' => $self->score(),
		));
	}

	return "<object id=\"smotriComVideoPlayer\" classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" width=\"{$width}\" height=\"360\"><param name=\"movie\" value=\"http://pics.smotri.com/player.swf?file={$id}&bufferTime=3&autoStart=false&str_lang=rus&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml\" /><param name=\"allowScriptAccess\" value=\"always\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"bgcolor\" value=\"#ffffff\" /><embed src=\"http://pics.smotri.com/player.swf?file={$id}&bufferTime=3&autoStart=false&str_lang=rus&xmlsource=http%3A%2F%2Fpics.smotri.com%2Fcskins%2Fblue%2Fskin_color.xml&xmldatasource=http%3A%2F%2Fpics.smotri.com%2Fskin_ng.xml\" quality=\"high\" allowscriptaccess=\"always\" allowfullscreen=\"true\" wmode=\"opaque\"  width=\"{$width}\" height=\"{$height}\" type=\"application/x-shockwave-flash\"></embed></object>";
}
