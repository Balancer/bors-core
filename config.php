<?php

/*
	Настройки фреймворка по умолчанию.
*/

config_set('admin_config_class', 'bors_admin_config');

// Если не ноль, то боты, при превышении LoadAverage данной величины,
// получают сообщение о временной недоступности сервиса
config_set('bot_lavg_limit', 0);

config_set('debug_class_load_trace', true);
if(empty($_SERVER['DOCUMENT_ROOT']))
	config_set('debug_hidden_log_dir', BORS_SITE.'/logs');
else
	config_set('debug_hidden_log_dir', dirname($_SERVER['DOCUMENT_ROOT']).'/logs');

config_set('default_template', 'default/index.html');

config_set('main_bors_db', 'BORS');
config_set('bors_core_db', 'BORS');
config_set('bors_logs_db', 'BORS_LOGS');

config_set('bors_version_show', false);

config_set('storage_db_sqlite_main', BORS_SITE.'/data/main.sqlite');

config_set('lcml_sharp_markup', false);
config_set('temporary_file_contents', @file_get_contents(dirname(__FILE__).'/resources/temporary.html'));

// Максимальные ширина, высота и площадь картинки для обработки
// При их преышении ресайз картинки не производится
// TODO: сделать очередь системной обработки больших картинок
config_set('images_resize_max_width', 2048);
config_set('images_resize_max_height', 2048);
config_set('images_resize_max_area', 5000000);
config_set('images_resize_filesize_enabled', 1048576);

config_set('image_transform_engine', 'GD');
config_set('url_truncate', false);
config_set('upload_dir', 'uploads');

// Кодировки
if(!config('internal_charset'))
	config_set('internal_charset', ini_get('default_charset'));		// Внутренняя кодировка фреймворка, обычно равна системной
if(!config('internal_charset'))
	config_set('internal_charset', 'utf-8');		//	Если системная не указана, то считаем utf-8. Исправить на учёт наличия mb_* функций
if(!config('output_charset'))
	config_set('output_charset', 'utf-8');			// Кодировка, в которой данные отдаются браузеру и сохраняются в статический кеш
if(!config('db_charset'))
	config_set('db_charset', 'utf-8');				// Кодировка БД
if(!config('locale'))
	config_set('locale', 'ru_RU.UTF-8');

config_set('3rdp_xmlrpc_path', 'xmlrpc-2.2.2');

if(!config('project.name'))
	config_set('project.name', strtolower(basename(dirname(BORS_SITE))));

// После установки кодировок -- использует internal_charset
if(!config('cache_dir'))
{
	$cache_dirs_parts = array();
	if(empty($_SERVER['HTTP_HOST']))
		$cache_dirs_parts[] = 'cli';
	else
		$cache_dirs_parts[] = strtolower($_SERVER['HTTP_HOST']);

	$cache_dirs_parts[] = config('project.name');

	if(!empty($_SERVER['USER']))
		$cache_dirs_parts[] = strtolower($_SERVER['USER']);
	$cache_dirs_parts[] = config('internal_charset');

	config_set('cache_dir', '/tmp/bors-cache/'.join('-', array_filter($cache_dirs_parts)));
}

config_set('cache_code_monolith', 0);

config_set('cache.webroot_dir', $_SERVER['DOCUMENT_ROOT'].'/cache');
config_set('cache.webroot_url', "/cache");
config_set('sites_store_path', $_SERVER['DOCUMENT_ROOT'].'/sites');
config_set('sites_store_url', 'http://'.@$_SERVER['HTTP_HOST'].'/sites');

@include_once(BORS_3RD_PARTY.'/config.php');
