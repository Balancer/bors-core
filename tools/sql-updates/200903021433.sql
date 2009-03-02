ALTER TABLE `bors_cross` ADD `type_id` INT UNSIGNED NULL AFTER `id` ;
ALTER TABLE `bors_cross` ADD INDEX ( `type_id` ) ;
