-- USE BORS;
ALTER TABLE `bors_images` ADD `full_file_name` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL AFTER `modify_time` ;
ALTER TABLE `bors_images` ADD UNIQUE (`full_file_name` ) ;

-- USE CACHE;
ALTER TABLE `bors_pictures_thumbs` ADD `full_file_name` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL AFTER `modify_time` ;
ALTER TABLE `bors_pictures_thumbs` ADD UNIQUE ( `full_file_name` );
