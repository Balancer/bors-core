ALTER TABLE `bors_cross` ADD `is_auto` TINYINT (1) UNSIGNED NOT NULL COMMENT 'Выставляется, если связь была получена автоматически.' AFTER `type_id`,
ADD INDEX ( `is_auto` );
