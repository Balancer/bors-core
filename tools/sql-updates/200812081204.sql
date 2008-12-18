ALTER TABLE `cache_groups` CHANGE `class_name` `_target_class_id` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
CHANGE `class_id` `_target_object_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';

UPDATE IGNORE `cache_groups` AS cg INNER JOIN BORS.bors_class_names AS cn ON cg._target_class_id = cn.name SET cg._target_class_id = cn.id;

ALTER TABLE `cache_groups` CHANGE `_target_class_id` `_target_class_id` INT( 64 ) UNSIGNED NOT NULL DEFAULT '0';

DELETE FROM `cache_groups` WHERE `_target_class_id` = 0;
