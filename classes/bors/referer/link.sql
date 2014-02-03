CREATE TABLE IF NOT EXISTS `bors_referer_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `create_time` int(10) unsigned NOT NULL,
  `modify_time` int(10) unsigned NOT NULL,
  `referer_normalized_url` VARCHAR(1023) NOT NULL,
  `target_class_name` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `target_object_id` varchar(255) DEFAULT NULL,
  `target_page` int(10) unsigned DEFAULT '0',
  `count` int(10) unsigned NOT NULL,
  `target_url` VARCHAR(4095) NOT NULL,
  `referer_original_url` VARCHAR(8191) NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referer_normalized_url` (`referer_normalized_url`(133),`target_class_name`,`target_object_id`(133),`target_page`),
  KEY `create_time` (`create_time`),
  KEY `modify_time` (`modify_time`),
  KEY `count` (`count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

