CREATE TABLE IF NOT EXISTS `bors_versioning` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `object_id` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `property_name` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `value` longtext NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `owner_id` int(11) NOT NULL,
  `moderator_id` int(11) NOT NULL,
  `is_approved` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_name` (`class_name`,`object_id`,`version`,`property_name`),
  KEY `is_approved` (`is_approved`),
  KEY `create_time` (`create_time`),
  KEY `class_name_2` (`class_name`,`object_id`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

