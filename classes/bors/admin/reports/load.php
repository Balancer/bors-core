<?php

class bors_admin_reports_load extends base_page
{
	function title() { return ec('Загрузка системы на ').date('d.m.Y H:i'); }
	function config_class() { return config('admin_config_class'); }
	function template() { return 'forum/_header.html'; }

	function body_data()
	{
		$dbh = new driver_mysql(config('bors_core_db'));
		return array(
			'total_time' => $dbh->select('bors_access_log', 'SUM(operation_time)', array()),

			'max_cpu_by_user' => $dbh->select_array('bors_access_log',
				'user_ip, user_id, count(user_ip) as cnt, sum(operation_time) as su, is_bot, user_agent',
				array('group'=>'user_ip',
					'order' => '-su',
					'limit' => 20,
				)
			),
			'max_cpu_by_classes' => $dbh->select_array('bors_access_log',
				'class_name, count(class_name) as cnt, sum(operation_time) as su',
				array('group'=>'class_name',
					'order' => '-su',
					'limit' => 20,
				)
			),

			'max_cpu_by_combine' => $dbh->select_array('bors_access_log',
				'user_ip, class_name, user_id, count(*) as cnt, sum(operation_time) as su, is_bot, user_agent',
				array('group'=>'user_ip,class_name',
					'order' => '-su',
					'limit' => 20,
				)
			),

			'can_see_ip' => object_property(bors()->user(), 'is_coordinator'),
		);
	}

//	function cache_static() { return bors()->user() ? 0 : rand(60, 120); }
}
