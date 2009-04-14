ALTER TABLE `bors_cross` ADD `owner_id` INT NULL COMMENT 'Кто добавил эту связь. Если положительное - то ID добавившего пользователя. Если отрицательно - от ID добавляющего скрипта.';
ALTER TABLE `bors_cross` ADD `comment` VARCHAR( 255 ) NOT NULL ;

ALTER TABLE `bors_cross` ADD INDEX ( `owner_id` ) ;
ALTER TABLE `bors_cross` ADD INDEX ( `comment` ) ;
