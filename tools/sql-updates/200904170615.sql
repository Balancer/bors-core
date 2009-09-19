ALTER TABLE `bors_cross` ADD `create_time` INT NOT NULL AFTER `sort_order` ,
ADD `modify_time` INT NOT NULL AFTER `create_time` ;
ALTER TABLE `bors_cross` ADD INDEX ( `create_time` );
ALTER TABLE `bors_cross` ADD INDEX ( `modify_time` );
