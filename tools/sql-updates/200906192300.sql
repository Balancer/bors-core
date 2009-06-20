ALTER TABLE `bors_keywords_index` ADD `target_class_name` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL AFTER `target_class_id` ;
ALTER TABLE `bors_keywords_index` ADD INDEX ( `target_class_name` ) ;
