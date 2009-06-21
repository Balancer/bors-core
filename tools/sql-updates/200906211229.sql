ALTER TABLE `hts_data_child` ADD `sort_order` INT NULL ;
ALTER TABLE `hts_data_child` ADD INDEX ( `sort_order` ) ;

ALTER TABLE `hts_data_parent` ADD `sort_order` INT NULL ;
ALTER TABLE `hts_data_parent` ADD INDEX ( `sort_order` ) ;
