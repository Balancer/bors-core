-- BORS_CACHE
ALTER TABLE `cache_groups` CHANGE `cache_group` `cache_group` VARCHAR( 64 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';
ALTER TABLE `cache_groups` CHANGE `_target_class_id` `_target_class_id` VARCHAR( 64 ) NOT NULL;
ALTER TABLE `cache_groups` CHANGE `_target_object_id` `_target_object_id` VARCHAR( 186 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `cached_files` CHANGE `object_id` `object_id` VARCHAR( 255 ) NOT NULL;

