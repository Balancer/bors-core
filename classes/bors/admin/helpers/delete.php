<?php

// Для отработки ссылок вида http://…/?delete
// Направляем их сюда, а отсюда идёт редирект на delete_url() объекта.

class bors_admin_helpers_delete extends bors_admin_page
{
	function pre_show()
	{
		$url = bors()->request()->pure_url();
		if(!($x = bors_load_uri($url)))
			return bors_message(ec('Объект для ссылки ').$url.ec(' не найден'));

		if($x2 = $x->get('real_object'))
			$x = $x2;

		return go($x->admin()->delete_url());
	}
}
