ALTER TABLE `bors_cross` ADD `create_time` INT NULL;
ALTER TABLE `bors_cross` ADD `modify_time` INT NULL;

ALTER TABLE `bors_cross` ADD `target_create_time` INT NULL AFTER `modify_time` ,
ADD `target_modify_time` INT NULL AFTER `target_create_time` ,
ADD `target_time1` INT NULL AFTER `target_modify_time` ,
ADD `target_time2` INT NULL AFTER `target_time1` ;

ALTER TABLE `bors_cross` ADD INDEX ( `target_create_time` );
ALTER TABLE `bors_cross` ADD INDEX ( `target_modify_time` );
ALTER TABLE `bors_cross` ADD INDEX ( `target_time1` );
ALTER TABLE `bors_cross` ADD INDEX ( `target_time2` );

ALTER TABLE `bors_cross` ADD `was_moderated` INT NOT NULL AFTER `owner_id` ;
ALTER TABLE `bors_cross` ADD INDEX ( `was_moderated` ) ;
