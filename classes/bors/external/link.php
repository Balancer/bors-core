<?php

/**
	Работа с разными типами внешних ссылок

	Используются переменные конфигурации:
		external.links.storage_path = '/var/www/sites' - путь к хранилищу ресурсов по умолчанию
		external.links.storage_url  = 'http://site.ext/sites' - ссылка хранилища ресурсов по умолчанию
*/

class bors_external_link extends base_object_db
{
	function storage_engine() { return 'bors_storage_mysql'; }
	function db_name() { return 'BORS'; }
	function table_name() { return 'external_links'; }

	function table_fields()
	{
		return array(
			'id',
			'url',
			'content_type',
			'title',
			'title_hash',
			'size',
			'image_class_name',
			'image_id',
			'file_path',
			'last_check_time',
			'is_invalid',
			'create_time',
			'modify_time',
		);
	}

	static function default_storage_path() { return config('external.links.storage_path'); }
	static function default_storage_url()  { return config('external.links.storage_url'); }

	static function register($url) { return self::register_ex($url, self::default_storage_path(), self::default_storage_url()); }
	static function register_ex($url, $storage_path, $storage_url)
	{
		$link = bors_find_first('bors_external_link', array('url' => $url));
		if($link) // Ссылка уже есть в БД. Возвращаем результат.
			return $link; //TODO: сделать настраиваемую (можно отложенной задачей) проверку на корректность ссылки. Или продумать внешний процесс.

		$url_data = parse_url($url);
		if(empty($url_data['path']))
			$url_data['path'] = '/';

		if(preg_match('!/$!', $url_data['path']))
			$url_data['path'] .= 'index';

		if(preg_match('!^(www|ftp)\.([^\.]+\..+)$!', $url_data['host'], $m))
			$url_data['host'] = $m[2];

		echo "url=$url ";
		print_d($url_data);
		$file_name = basename(@$url_data['path']);
		$dir_name = dirname(@$url_data['path']);
		echo "dn=$dir_name, fn=$file_name\n";

		// Ссылки в БД не нашли. Нужно регистировать новую.
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_TIMEOUT => 5,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_ENCODING => 'gzip, deflate',
//			CURLOPT_RANGE => '0-4095',
//			CURLOPT_REFERER => $original_url,
			CURLOPT_AUTOREFERER => true,
//			CURLOPT_HTTPHEADER => $header,
			CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; FunWebProducts; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
			CURLOPT_RETURNTRANSFER => true,
		));

	if(preg_match("!lenta\.ru!", $url))
		curl_setopt($ch, CURLOPT_PROXY, 'home.balancer.ru:3128');

	$data = curl_exec($ch);

//	print_r($data);

	curl_close($ch);

	return $data;
	}
}
