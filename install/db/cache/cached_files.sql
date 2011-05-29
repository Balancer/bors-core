CREATE TABLE IF NOT EXISTS `cached_files` (
  `file` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `original_uri` varchar(255) NOT NULL DEFAULT '',
  `last_compile` int(11) NOT NULL DEFAULT '0',
  `expire_time` int(11) NOT NULL DEFAULT '0',
  `class_name` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `class_id` int(10) unsigned NOT NULL,
  `object_id` int(11) NOT NULL,
  `recreate` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`file`),
  KEY `uri` (`uri`),
  KEY `original_uri` (`original_uri`),
  KEY `last_compile` (`last_compile`),
  KEY `expire_time` (`expire_time`),
  KEY `class_id` (`class_id`),
  KEY `object_id` (`object_id`),
  KEY `file` (`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
