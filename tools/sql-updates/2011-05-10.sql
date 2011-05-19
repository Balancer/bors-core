-- В БД BORS. Дополнительные поля переменных
ALTER TABLE `bors_server_vars` ADD `title` VARCHAR( 255 ) NULL AFTER `name`;

