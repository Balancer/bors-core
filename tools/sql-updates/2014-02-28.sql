-- CACHE
ALTER TABLE `cached_files` ADD `target_page` INT UNSIGNED NULL AFTER `object_id`;
ALTER TABLE `cached_files` DROP INDEX file;

ALTER TABLE `cache_groups` ADD `target_class_name` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL AFTER  `cache_group`;
ALTER TABLE `cache_groups` ADD `target_page` INT UNSIGNED NULL AFTER  `_target_object_id`;
