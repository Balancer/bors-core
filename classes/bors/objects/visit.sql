CREATE TABLE IF NOT EXISTS `bors_views_count` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target_class_name` varchar(64) NOT NULL,
  `target_id` varchar(255) NOT NULL,
  `target_page` int(11) NOT NULL,
  `views_count` int(11) NOT NULL,
  `first_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `views_average_per_day` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `target_class_name_2` (`target_class_name`,`target_id`,`target_page`),
  KEY `views_average_per_day` (`views_average_per_day`),
  KEY `views_count` (`views_count`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
