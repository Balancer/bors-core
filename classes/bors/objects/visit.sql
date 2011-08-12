-- DB: BORS

CREATE TABLE IF NOT EXISTS `bors_views_count` (
  `target_class_name` varchar(64) NOT NULL,
  `target_id` varchar(255) NOT NULL,
  `views_count` int(11) NOT NULL,
  `first_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_visit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `views_average_per_day` float NOT NULL,
  PRIMARY KEY (`target_class_name`,`target_id`),
  KEY `views_average_per_day` (`views_average_per_day`),
  KEY `views_count` (`views_count`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
