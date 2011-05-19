-- В БД BORS. Дополнительные поля переменных
ALTER TABLE `bors_server_vars` CHANGE `name` `name` VARCHAR(255);
ALTER TABLE `bors_server_vars` ADD `type` VARCHAR(255) COMMENT 'Тип переменной' AFTER `value`;
