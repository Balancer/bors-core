CREATE TABLE `bors_recommendations` IF NOT EXISTS (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_class_name` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  `target_class_name` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `comment` text,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `owner_class_name` (`owner_class_name`,`owner_id`,`target_class_name`,`target_id`)
) DEFAULT CHARSET=utf8;
