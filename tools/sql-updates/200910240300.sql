-- USE BORS;
ALTER TABLE `bors_images` ADD `full_url` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_ci NULL AFTER `full_file_name` ;
ALTER TABLE `bors_images` ADD UNIQUE (`full_url` ) ;

alter table bors_access_log add key uip_at (user_ip, access_time);
