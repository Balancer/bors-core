<?php

class bors_votes_last extends base_page
{
	function title() { return ec('Оценки сообщений'); }
	function config_class() { return 'airbase_board_config'; }
	function body_engine() { return 'body_php'; }

	function local_data()
	{
		$last = objects_array('bors_votes_thumb', array(
				'order' => '-create_time',
				'limit' => 30,
		));

		$best = objects_array('bors_votes_thumb', array(
				'group' => 'target_class_name,target_object_id',
				'order' => 'SUM(score) DESC',
				'limit' => 50,
		));

		$best_of_month = objects_array('bors_votes_thumb', array(
				'create_time>' => time()-86400*30,
				'group' => 'target_class_name,target_object_id',
				'order' => 'SUM(score) DESC',
				'limit' => 50,
		));

		$differents = objects_array('bors_votes_thumb', array(
				'create_time>' => time()-86400*30,
				'group' => 'target_class_name,target_object_id',
				'order' => 'SUM(ABS(score))/(SUM(score) + 1) DESC',
				'limit' => 10,
		));

		bors_objects_targets_preload($last);
		bors_objects_targets_preload($best);
		bors_objects_targets_preload($best_of_month);
		bors_objects_targets_preload($differents);

		return array(
			'last' => $last,
			'total' => objects_count('bors_votes_thumb', array()),
			'best' => $best,
			'best_of_month' => $best_of_month,
			'differents' => $differents,
		);
	}

	function pre_show()
	{
		templates_noindex();

		if(bors()->client()->is_bot())
			return go('/');

		return false;
	}

	function cache_static() { return rand(60, 120); }
}
