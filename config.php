<?php

/*
	Настройки фреймворка по умолчанию.
*/

config_set('admin_config_class', 'bors_admin_config');

// Если не ноль, то боты, при превышении LoadAverage данной величины,
// получают сообщение о временной недоступности сервиса
config_set('bot_lavg_limit', 0);

config_set('cache_dir', '/tmp/bors-cache-'.@$_SERVER['HTTP_HOST']);

config_set('debug_class_load_trace', true);
config_set('debug_hidden_log_dir', realpath($_SERVER['DOCUMENT_ROOT'].'/logs'));

config_set('default_template', 'default/index.html');

config_set('smarty_path', 'smarty-2.6.26');
config_set('main_bors_db', 'BORS');
config_set('bors_core_db', 'BORS');

config_set('bors_version_show', '2');

config_set('storage_db_sqlite_main', BORS_SITE.'/data/main.sqlite');

config_set('lcml_sharp_markup', false);
config_set('lcml.code.engines_order', 'lcml_tag_code_geshi');
config_set('lcml.code.geshi.base_dir', 'geshi-1.0.8.4');
config_set('lcml.request.charset_default', 'WINDOWS-1251');
config_set('temporary_file_contents', file_get_contents(dirname(__FILE__).'/resources/temporary.html'));

// Максимальные ширина, высота и площадь картинки для обработки
// При их преышении ресайз картинки не производится
// TODO: сделать очередь системной обработки больших картинок
config_set('images_resize_max_width', 2048);
config_set('images_resize_max_height', 2048);
config_set('images_resize_max_area', 5000000);
config_set('images_resize_filesize_enabled', 1048576);

config_set('image_transform_engine', 'GD');
config_set('url_truncate', false);

// Кодировки
config_set('internal_charset', 'utf-8');		// Внутренняя кодировка фреймворка, обычно равна системной
config_set('output_charset', 'utf-8');			// Кодировка, в которой данные отдаются браузеру и сохраняются в статический кеш
config_set('db_charset', 'utf-8');				// Кодировка БД
config_set('locale', 'ru_RU.UTF-8');

config_set('3rdp_xmlrpc_path', 'xmlrpc-2.2.2');

@include_once(BORS_3RD_PARTY.'/config.php');
