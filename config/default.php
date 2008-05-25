<?php

// Если не ноль, то боты, при превышении LoadAverage данной величины, получают сообщение о временной недоступности сервиса
config_set('bot_lavg_limit', 0);
config_set('cache_dir', '/var/www/localhost/cache');

config_set('debug_class_load_trace', true);

config_set('smarty_path', 'smarty');
config_set('main_bors_db', 'BORS');
config_set('bors_core_db', 'BORS');
