ALTER TABLE `bors_keywords` ADD `description` TEXT NULL ,
ADD `synonym_to` INT UNSIGNED NULL;
ALTER TABLE `bors_keywords` ADD INDEX ( `synonym_to` ) ;

ALTER TABLE `bors_keywords_index` ADD `target_forum_id` INT UNSIGNED NULL ,
ADD `target_container_class_name` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL ,
ADD `target_container_class_id` INT UNSIGNED NULL ,
ADD `target_container_object_id` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL 
