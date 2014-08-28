-- USE CACHE;
ALTER TABLE `bors_pictures_thumbs` ADD `full_url` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL AFTER `full_file_name` ;
ALTER TABLE `bors_pictures_thumbs` ADD UNIQUE (`full_url` ) ;
