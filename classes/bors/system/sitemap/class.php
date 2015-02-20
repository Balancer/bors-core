<?php

/*
	Возвращает список sitemap-файлов конкретных объектов с разбивкой
	по некоторому sitemap-id. Например, по номеру страниц или по дате
*/

class bors_system_sitemap_class extends bors_xml
{
	function body_data()
	{
		$map = array();

		$class_name = $this->id();

		$ids = call_user_func(array($class_name, 'sitemap_ids'));

		foreach($ids as $id)
		{
			$last = call_user_func(array($class_name, 'sitemap_last_modified'), $id);

			if(!$last)
				continue;

			$map["http://{$_SERVER['HTTP_HOST']}/sitemap-{$class_name}-{$id}.xml"] = $last;
		}

		return compact('map');
	}

	function cache_static() { return rand(300, 1200); }
}
