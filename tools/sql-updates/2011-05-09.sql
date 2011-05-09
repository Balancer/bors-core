-- В БД BORS. Дополнительные поля переменных
ALTER TABLE `bors_logging` ADD `owner_ip` VARCHAR( 16 ) NULL AFTER `last_editor_id`;
ALTER TABLE `bors_logging` ADD `action_url` TEXT NULL;

