<?php
function smarty_modifier_lcmlbb($string)
{
	require_once('funcs/lcml.php');

	$ch = &new Cache();
	if($ch->get('smarty-modifiers-lcmlbb-compiled', $string))
		return $ch->last();

	return $ch->set(lcml($string, 
		array(
			'cr_type' => 'save_cr',
			'forum_type' => 'punbb',
//			'forum_base_uri' => 'http://balancer.ru/forum',
			'sharp_not_comment' => true,
			'html_disable' => true,
//			'uri' => "post://{$cur_post['id']}/",
	)), 7*86400);
}
