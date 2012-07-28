-- BORS
ALTER TABLE `bors_images` CHANGE `full_file_name` `full_file_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	CHANGE `full_url` `full_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- CACHE
ALTER TABLE `bors_pictures_thumbs` CHANGE `full_file_name` `full_file_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
	CHANGE `full_url` `full_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

