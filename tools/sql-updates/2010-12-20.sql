-- DB: SEARCH
ALTER TABLE `bors_search_source_0` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_0` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_1` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_1` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_2` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_2` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_3` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_3` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_4` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_4` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_5` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_5` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_6` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_6` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_7` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_7` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_8` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_8` ADD INDEX ( `target_weight` ) ;
ALTER TABLE `bors_search_source_9` ADD `target_weight` INT NOT NULL AFTER `count` ;
ALTER TABLE `bors_search_source_9` ADD INDEX ( `target_weight` ) ;

ALTER TABLE `bors_search_titles` ADD `target_weight` INT NOT NULL ;
ALTER TABLE `bors_search_titles` ADD INDEX ( `target_weight` );
