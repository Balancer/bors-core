RENAME TABLE `WWW`.`keywords` TO `WWW`.`bors_keywords` ;
RENAME TABLE `WWW`.`keywords_map` TO `WWW`.`bors_keywords_index` ;

ALTER TABLE `bors_keywords_index` ADD `target_class_name` VARCHAR( 64 ) CHARACTER SET binary NULL AFTER `target_class_id` ;
ALTER TABLE `bors_keywords_index` ADD INDEX ( `target_class_name` ) ;
