-- BORS
ALTER TABLE  `bors_access_log` CHANGE  `is_bot`  `is_bot` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `bors_access_log` CHANGE `user_id` `user_id` INT UNSIGNED NULL;
ALTER TABLE `bors_access_log` CHANGE `object_id` `object_id` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;
ALTER TABLE `bors_access_log` CHANGE `referer` `referer` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;
ALTER TABLE `bors_access_log` CHANGE `user_agent` `user_agent` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;

-- CACHE
ALTER TABLE `cached_files` CHANGE `object_id` `object_id` INT(11) NULL;
