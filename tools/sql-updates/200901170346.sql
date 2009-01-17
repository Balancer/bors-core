use BORS;
ALTER TABLE `bors_images` ADD INDEX ( `relative_path` );
ALTER TABLE `bors_images` ADD INDEX ( `file_name` );
