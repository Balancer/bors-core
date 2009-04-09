<?php

// Если не ноль, то боты, при превышении LoadAverage данной величины, получают сообщение о временной недоступности сервиса
config_set('bot_lavg_limit', 0);
config_set('cache_dir', $_SERVER['DOCUMENT_ROOT'].'/../.cache');

config_set('debug_class_load_trace', true);

config_set('smarty_path', 'smarty-2.6.21');
config_set('main_bors_db', 'BORS');
config_set('bors_core_db', 'BORS');

config_set('bors_version_show', '2');

config_set('lcml_sharp_markup', false);
config_set('temporary_file_contents', file_get_contents(dirname(__FILE__).'/../resources/temporary.html'));

config_set('images_resize_max_width', 2048);
config_set('images_resize_max_height', 2048);

// Кодировки
config_set('internal_charset', 'utf-8');
config_set('db_charset', 'utf-8');
config_set('default_character_set', 'utf-8');
config_set('locale', 'ru_RU.UTF-8');
