<?php

class bors_lcml_tag_pair_youtube extends bors_lcml_tag_pair
{
	function html($id, &$params=array())
	{
		$width  = @$params['width']  ? $params['width']  : '640';
		$height = @$params['height'] ? $params['height'] : '390';

		self::register($id, $params);

		return "<div class=\"rs_box\" style=\"width: {$width}px;\"><iframe width=\"{$width}\" height=\"{$height}\" src=\"http://www.youtube.com/embed/{$id}\" frameborder=\"0\" allowfullscreen></iframe><br/><small class=\"inbox\"><a href=\"http://www.youtube.com/watch/?v={$id}\">http://www.youtube.com/watch/?v={$id}</a></small></div>";
	}

	function text($id, &$params=array())
	{
		self::register($id, $params);

		return ec("http://www.youtube.com/watch?v={$id}\n");
	}

	function register($id, $params)
	{
		if(!($self = defval($params, 'self')))
			return;

		if($self->class_name() != 'balancer_board_post' && $self->class_name() != 'forum_post')
			return;

		if(bors_find_first('balancer_board_posts_object', array(
			'post_id' => $self->id(),
			'target_class_name' => 'bors_external_youtube',
			'target_object_id' => $id,
		)))
			return;

		bors_new('balancer_board_posts_object', array(
			'post_id' => $self->id(),
			'target_class_id' => class_name_to_id('bors_external_youtube'),
			'target_class_name' => 'bors_external_youtube',
			'target_object_id' => $id,
			'target_create_time' => $self->create_time(),
			'target_score' => $self->score(),
		));
	}

	static function __unit_test($suite)
	{
		$url = 'http://www.youtube.com/watch?v=IO2h-yzTOhs&list=UUNtclu0DvBOkjbVYhZZUcdA&index=4&feature=plcp';
		$html = bors_external_youtube::url2html($url);
		$suite->assertRegexp('/<iframe.*v=IO2h-yzTOhs/', $html);
	}
}
