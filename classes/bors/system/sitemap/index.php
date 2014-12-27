<?php

/*
	Возвращает sitemap.xml со списком файлов с sitemap конкретных классов.
	Например: http://forums.balancer.ru/sitemap-index.xml
*/

class bors_system_sitemap_index extends bors_xml
{
	function body_data()
	{
		$class_data = array();
		if(config('sitemap.classes'))
		{
			foreach(preg_split('/[,\s]+/', config('sitemap.classes')) as $class_name)
			{
				$last = bors_find_first($class_name, array('order' => '-modify_time'));
				if(!$last)
					continue;

				$last->set_attr('sitemap_class_index_url', "http://{$_SERVER['HTTP_HOST']}/sitemap-{$class_name}.xml");
				$class_data[$class_name] = $last;
			}
		}

		return compact('class_data');
	}

	function cache_static() { return rand(60, 120); }
}
