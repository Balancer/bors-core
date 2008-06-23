<?php
function smarty_modifier_lcmlbb($string, $nocache = false)
{
	require_once('engines/lcml.php');

	$ch = $nocache ? NULL : new bors_cache();
	if($ch && $ch->get('smarty-modifiers-lcmlbb-compiled', $string))
		return $ch->last();

	$string = lcml($string, 
		array(
			'cr_type' => 'save_cr',
			'forum_type' => 'punbb',
//			'forum_base_uri' => 'http://balancer.ru/forum',
			'sharp_not_comment' => true,
			'html_disable' => true,
			'nocache' => $nocache,
//			'uri' => "post://{$cur_post['id']}/",
	));

	if($ch)
		$ch->set($string, 7*86400);

	return $string;
}
